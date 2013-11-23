<?php
class TrueAction_FileTransfer_Helper_Data extends Mage_Core_Helper_Abstract
{
	protected $_configErrorClass = 'Exception';

	const GLOBAL_DEFAULT_PROTOCOL = 'filetransfer/global/default_protocol';
	const GLOBAL_SORT_ORDER       = 'filetransfer/global/sort_order';
	const GLOBAL_SHOW_IN_DEFAULT  = 'filetransfer/global/show_in_default';
	const GLOBAL_SHOW_IN_WEBSITE  = 'filetransfer/global/show_in_website';
	const GLOBAL_SHOW_IN_STORE    = 'filetransfer/global/show_in_store';

	/**
	 * Transmit a file to a remote host.
	 * @param  string $localFile
	 * @param  string $remoteFile
	 * @param  string $configPath see README.md
	 * @param  mixed  $store
	 * @return boolean
	 */
	public function sendFile($localFile, $remoteFile, $configPath, $store=null)
	{
		$protocol = $this->getProtocolModel($configPath, $store);
		return $protocol->sendFile($localFile, $remoteFile);
	}

	/**
	 * Retrieve a file from a remote host.
	 * @param  string $localFile
	 * @param  string $remoteFile
	 * @param  string $configPath see README.md
	 * @param  mixed  $store
	 * @return boolean
	 */
	public function getFile($localFile, $remoteFile, $configPath, $store=null)
	{
		$protocol = $this->getProtocolModel($configPath, $store);
		return $protocol->getFile($localFile, $remoteFile);
	}

	/**
	 * Delete the given file from the remote.
	 * @param  string $remoteFile Remote location of the file
	 * @param  string $configPath @see README.md
	 * @param  mixed  $store      Any valid store reference
	 * @return boolean            Success or failure
	 */
	public function deleteFile($remoteFile, $configPath, $store=null)
	{
		$protocol = $this->getProtocolModel($configPath, $store);
		return $protocol->deleteFile($remoteFile);
	}

	/**
	 * Get all files in the given directory matching the pattern.
	 * @param  string $localDir   Local directory where files should be placed
	 * @param  string $remoteDir  Remote directory to copy files from
	 * @param  string $pattern    Glob file name must match
	 * @param  string $configPath @see README.md
	 * @param  mixed  $store      Any valid store reference
	 * @return boolean            Success or failure
	 */
	public function getAllFiles($localDir, $remoteDir, $pattern, $configPath, $store=null)
	{
		$protocol = $this->getProtocolModel($configPath, $store);
		return $protocol->getAllFiles($localDir, $remoteDir, $pattern);
	}

	/**
	 * Transmit a string and save it to a file on a remote host.
	 * @param  string $localFile
	 * @param  string $remoteFile
	 * @param  string $configPath see README.md
	 * @param  mixed  $store
	 * @return boolean
	 */
	public function sendString($dataString, $remoteFile, $configPath, $store=null)
	{
		$protocol = $this->getProtocolModel($configPath, $store);
		return $protocol->sendString($dataString, $remoteFile);
	}

	/**
	 * Retrieve a file from a remote host as a string.
	 * returns false on failure.
	 * @param  string $localFile
	 * @param  string $remoteFile
	 * @param  string $configPath see README.md
	 * @param  mixed  $store
	 * @return string|boolean
	 */
	public function getString($remoteFile, $configPath, $store=null)
	{
		$protocol = $this->getProtocolModel($configPath, $store);
		return $protocol->getString($remoteFile);
	}

	/**
	 * Return the data needed to instantiate and
	 * configure the appropriate protocol model.
	 * @param  string                $configPath
	 * @param  string                $protocol
	 * @param  Mage_Core_Model_Store $store
	 * @return array
	 */
	public function getInitData($configPath, $protocol=null, $store=null)
	{
		// not having the config path set is a non-recoverable error since there
		// is currently no way to figure which set of data to get otherwise.
		if (!$configPath) {
			Mage::throwException('FileTransfer Config Error: config path not set');
			// @codeCoverageIgnoreStart
		}
		// @codeCoverageIgnoreEnd
		// if the protocol code was not specified, try reading it from the mage config.
		if (!$protocol){
			$protocol = Mage::getStoreConfig(
				$configPath . '/filetransfer_protocol',
				$store
			);
		}
		if (!$protocol) {
			$protocol = Mage::helper('filetransfer')->getDefaultProtocol();
		}
		return array(
			'store'         => $store,
			'config_path'   => $configPath,
			'protocol_code' => $protocol
		);
	}

	/**
	 * Return the model for the configured protocol.
	 */
	public function getProtocolModel($configPath, $protocol=null, $store=null)
	{
		$config = $this->getInitData(
			$configPath,
			$protocol,
			$store
		);
		return Mage::getModel(
			'filetransfer/protocol_types_' . $config['protocol_code'],
			array('config' => $config)
		);
	}

	/**
	 * Scan the Protocol/Types directory for php files and use
	 * their lowercased basename to get a list of protocol codes.
	 */
	public function getProtocolCodes()
	{
		return TrueAction_FileTransfer_Model_Protocol_Abstract::getCodes();
	}

	/**
	 * Return the default protocol to use when sending files.
	 * @param Mage_Core_Model_Store
	 * @return string
	 */
	public function getDefaultProtocol($store=null)
	{
		return Mage::getStoreConfig(self::GLOBAL_DEFAULT_PROTOCOL, $store);
	}
	/**
	 * Return the default initial sort order
	 * for dynamically inserted config fields.
	 * @param Mage_Core_Model_Store
	 * @return string
	 */
	public function getGlobalSortOrder($store=null)
	{
		return Mage::getStoreConfig(self::GLOBAL_SORT_ORDER, $store);
	}
	/**
	 * Return the default show_in_default value
	 * for dynamically inserted config fields.
	 * @param Mage_Core_Model_Store
	 * @return string
	 */
	public function getGlobalShowInDefault($store=null)
	{
		return Mage::getStoreConfig(self::GLOBAL_SHOW_IN_DEFAULT, $store);
	}
	/**
	 * Return the default show_in_website value
	 * for dynamically inserted config fields.
	 * @param Mage_Core_Model_Store
	 * @return string
	 */
	public function getGlobalShowInWebsite($store=null)
	{
		return Mage::getStoreConfig(self::GLOBAL_SHOW_IN_WEBSITE, $store);
	}
	/**
	 * Return the default show_in_store value
	 * for dynamically inserted config fields.
	 * @param Mage_Core_Model_Store
	 * @return string
	 */
	public function getGlobalShowInStore($store=null)
	{
		return Mage::getStoreConfig(self::GLOBAL_SHOW_IN_STORE, $store);
	}
}
