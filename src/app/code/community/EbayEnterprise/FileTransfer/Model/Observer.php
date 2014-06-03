<?php
/**
 * Copyright (c) 2013-2014 eBay Enterprise, Inc.
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @copyright   Copyright (c) 2013-2014 eBay Enterprise, Inc. (http://www.ebayenterprise.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class EbayEnterprise_FileTransfer_Model_Observer
{
	// config path to the filetransfer registration
	const CONFIG_PATH_REGISTRY = 'filetransfer/registry';
	// event to dispatch after import is complete
	const IMPORT_COMPLETE_EVENT = 'filetransfer_import_complete';
	// event to dispatch after export is complete
	const EXPORT_COMPLTE_EVENT = 'filetransfer_export_complete';
	// permissions mode to use when creating directories
	const CREATE_DIRECTORY_MODE = 0750;
	const MISSING_FIELD_MESSAGE = '[%s] Configured remote directory pair is missing %s.';

	public function handleConfigImport($observer)
	{
		$event = $observer->getEvent();
		$injector = $event->getInjector();
		$helper = Mage::helper('filetransfer');
		foreach ($helper->getProtocolCodes() as $protocol) {
			$config = $helper->getProtocolModel(
				$event->getConfigPath(),
				$protocol
			)->getConfigModel();
			$fields = $config->generateFields($event->getModuleSpec());
			$injector->insertConfig($fields);
		}
	}
	/**
	 * Get all of the filetransfer configuration from various other modules
	 * and process the imports and exports.
	 * @return self
	 */
	public function runTransfers()
	{
		Mage::log(sprintf('[%s] Starting all transfers', __CLASS__), Zend_Log::DEBUG);
		$remoteHostConfigPaths = $this->_getRegisteredConfigurations();
		$this->_importFiles($remoteHostConfigPaths)
			->_exportFiles($remoteHostConfigPaths);
		Mage::log(sprintf('[%s] Finished all transfers', __CLASS__), Zend_Log::DEBUG);
		return $this;
	}
	/**
	 * Get all of the FileTransfer config paths registered via the
	 * filetransfer/registry
	 * @return array
	 */
	protected function _getRegisteredConfigurations()
	{
		return $this->_lookupConfig(self::CONFIG_PATH_REGISTRY) ?: array();
	}
	/**
	 * Import files to from all configured remote directories - get all files from
	 * the configured remote directories, dispatch the
	 * `filetransfer_import_complete` event, and then delete all of the files
	 * from the remote system.
	 * @param  array  $remoteHostConfigPaths
	 * @return self
	 */
	protected function _importFiles(array $remoteHostConfigPaths=array())
	{
		Mage::log(sprintf('[%s] Importing files from hosts', __CLASS__), Zend_Log::DEBUG);
		return $this->_doForHostConfigs(
			$remoteHostConfigPaths,
			'filetransfer_imports',
			array($this, '_importFromRemote')
		)->_dispatchEvent(self::IMPORT_COMPLETE_EVENT);
	}
	/**
	 * Export files to the configured hosts - get all files using the local
	 * directories, trigger the `filetransfer_export_complete` event, and
	 * then move all of the files to the configured "send" directory.
	 * @param  array  $remoteHostConfigPaths
	 * @return self
	 */
	protected function _exportFiles(array $remoteHostConfigPaths=array())
	{
		Mage::log(sprintf('[%s] Exporting files from hosts', __CLASS__), Zend_Log::DEBUG);
		return $this->_doForHostConfigs(
			$remoteHostConfigPaths,
			'filetransfer_exports',
			array($this, '_exportToRemote')
		)->_dispatchEvent(self::EXPORT_COMPLTE_EVENT);
	}
	/**
	 * Perform the given callback on all remote host configs that have the given
	 * config field.
	 * @param  array    $remoteHostConfigPaths     Array of remote host config data
	 * @param  string   $hostConfigField Config field required for processing - filter host config to only hosts that have this field
	 * @param  callable $callback        Anything that may be passed as a callback
	 * @return self
	 */
	private function _doForHostConfigs(array $remoteHostConfigPaths, $hostConfigField, $callback)
	{
		foreach ($remoteHostConfigPaths as $hostPath) {
			$hostConfigData = $this->_lookupConfig($hostPath);
			if (isset($hostConfigData[$hostConfigField]) && $hostConfigData[$hostConfigField]) {
				call_user_func($callback, $hostPath, $hostConfigData);
			}
		}
		return $this;
	}
	/**
	 * Import all configured sets of files from the remote host.
	 * Should get all files from all given remote directories and then delete
	 * any files retrieved from the remote.
	 * @param string $remoteHostConfigPath
	 * @param array  $configData
	 * @return self
	 */
	protected function _importFromRemote($remoteHostConfigPath, $configData)
	{
		Mage::log(sprintf('[%s] Importing for config: %s', __CLASS__, $remoteHostConfigPath));
		$protocol = Mage::helper('filetransfer')->getProtocolModel($remoteHostConfigPath);
		return $this->_doForRemoteConfig(
			array_filter($configData['filetransfer_imports'], array($this, '_isImportDirectoryPairValid')),
			array($protocol, 'getAllFiles'),
			function ($fileSet) use ($protocol) { $protocol->deleteFile($fileSet['remote']); }
		);
	}
	/**
	 * Export all files from the configured set of local directories to the
	 * corresponding remote directory. Any local files sent in this way should be
	 * moved to a known "sent" directory.
	 * @param string  $remoteHostConfigPath
	 * @param array   $configData
	 * @return self
	 */
	protected function _exportToRemote($remoteHostConfigPath, $configData)
	{
		Mage::log(sprintf('[%s] Exporting for config: %s', __CLASS__, $remoteHostConfigPath));
		$fileHelper = Mage::helper('filetransfer/file');
		$protocol = Mage::helper('filetransfer')->getProtocolModel($remoteHostConfigPath);
		return $this->_doForRemoteConfig(
			array_filter($configData['filetransfer_exports'], array($this, '_isExportDirectoryPairValid')),
			array($protocol, 'sendAllFiles'),
			function ($fileSet, $exportPair) use ($fileHelper) {
				$fileHelper->mvToDir(
					$fileSet['local'],
					$exportPair['sent_directory'] . DS . basename($fileSet['local'])
				);
			}
		);
	}
	/**
	 * Both import and export follow the same general process, for each configured
	 * pair of directories, call some method to get/send all files and get a
	 * list of files sent. Then for each file sent, perform some cleanup
	 * operation.
	 * @param  array    $configData            array of local/remote directory pairs
	 * @param  callable $transferCallable  callback to transfer files from each configured pair
	 * @param  callable $cleanupCallable   cleanup callable to call on each file transferred
	 * @return self
	 */
	private function _doForRemoteConfig(array $configData, $transferCallable, $cleanupCallable)
	{
		foreach ($configData as $dirPair) {
			$files = array();
			try {
				// get/set transfer callback - must always result in an array
				$files = (array) call_user_func_array(
					$transferCallable,
					// Only use the file_pattern argument when it is set in the config,
					// if it isn't set, callback should use the default param of the
					// method being called.
					array_filter(array(
						Mage::getBaseDir('var') . DS . $dirPair['local_directory'],
						$dirPair['remote_directory'],
						isset($dirPair['file_pattern']) ? $dirPair['file_pattern'] : null
					))
				);
			// For potentially transient errors, a transfer fails or connection fails,
			// log a warning and try to process the rest of the files directory pairs.
			} catch (EbayEnterprise_FileTransfer_Exception_Transfer $e) {
				Mage::log($e->getMessage(), Zend_Log::WARN);
			} catch (EbayEnterprise_FileTransfer_Exception_Connection $e) {
				Mage::log($e->getMessage(), Zend_Log::WARN);
			// Any other errors, auth or configuration, are unlikely to go away
			// without some level of human interaction so log the error more loudly
			// and do not attempt to process any other files for the configured
			// remot host.
			} catch (EbayEnterprise_FileTransfer_Exception_Base $e) {
				Mage::log($e->getMessage(), Zend_Log::CRIT);
				break;
			}
			foreach ($files as $file) {
				call_user_func_array($cleanupCallable, array($file, $dirPair));
			}
		}
		return $this;
	}
	/**
	 * Check for the export directory pairings to contain all necessary fields.
	 * @see self::_isDirectoryPairValid
	 * @param  array  $dirPair
	 * @return boolean
	 */
	protected function _isExportDirectoryPairValid($dirPair)
	{
		return $this->_isDirectoryPairValid($dirPair, true);
	}
	/**
	 * Check for the import directory pairings to contain all necessary fields.
	 * @see self::_isDirectoryPairValid
	 * @param  array  $dirPair
	 * @return boolean
	 */
	protected function _isImportDirectoryPairValid($dirPair)
	{
		return $this->_isDirectoryPairValid($dirPair);
	}
	/**
	 * Validate that each pair of directories contains a remote_directory
	 * and a local_directory. If validation is for an export pair, should also
	 * validate that the sent_directory is also set.
	 * If any are missing, an exception should be thrown.
	 * @param  array   $dirPair array of configured local/remote/sent directories
	 * @param  boolean $isExport Is validation for an export directory
	 * @return boolean true if directory pairing contains all necessary fields, false otherwise
	 */
	private function _isDirectoryPairValid($dirPair, $isExport=false)
	{
		// For imports, only need local and remote directory, for exports, also
		// need a local sent directory.
		$requiredFields = array_merge(
			array('local_directory', 'remote_directory'),
			$isExport ? array('sent_directory') : array()
		);
		if ($missingFields = array_diff($requiredFields, array_keys($dirPair))) {
			Mage::log(
				sprintf(static::MISSING_FIELD_MESSAGE, __METHOD__, implode(' and ', $missingFields)),
				Zend_Log::DEBUG
			);
			return false;
		}
		$this->_checkAndCreateDirs($dirPair);
		return true;
	}
	/**
	 * Make sure any local directories exist before attempting to import or export
	 * files to/from them.
	 * @param  array $dirPairs
	 * @return self
	 */
	protected function _checkAndCreateDirs($dirPair)
	{
		$file = Mage::getModel('Varien_Io_File');
		$file->open(array('path' => Mage::getBaseDir('var')));
		$file->checkAndCreateFolder(
			$file->getCleanPath(Mage::getBaseDir('var') . DS . $dirPair['local_directory']),
			self::CREATE_DIRECTORY_MODE
		);
		if (isset($dirPair['sent_directory'])) {
			$file->checkAndCreateFolder(
				$file->getCleanPath(Mage::getBaseDir('var') . DS . $dirPair['sent_directory']),
				self::CREATE_DIRECTORY_MODE
			);
		}
		return $this;
	}
	/**
	 * Get the configuration at the specified path.
	 * @param  string $path
	 * @return mixed
	 * @codeCoverageIgnore
	 */
	protected function _lookupConfig($path)
	{
		return Mage::app()->getStore()->getConfig($path);
	}
	/**
	 * Dispatch an event with the given name.
	 * @param  string $eventName
	 * @return self
	 * @codeCoverageIgnore
	 */
	protected function _dispatchEvent($eventName)
	{
		Mage::dispatchEvent($eventName);
		return $this;
	}
}
