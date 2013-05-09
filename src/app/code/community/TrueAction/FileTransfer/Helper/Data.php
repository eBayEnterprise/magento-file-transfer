<?php
class TrueAction_FileTransfer_Helper_Data extends Mage_Core_Helper_Abstract
{
	protected $_configErrorClass = 'Exception';

	const GLOBAL_DEFAULT_PROTOCOL = 'filetransfer/global/default_protocol';
	const GLOBAL_SORT_ORDER       = 'filetransfer/global/sort_order';
	const GLOBAL_SHOW_IN_DEFAULT  = 'filetransfer/global/show_in_default';
	const GLOBAL_SHOW_IN_WEBSITE  = 'filetransfer/global/show_in_website';
	const GLOBAL_SHOW_IN_STORE    = 'filetransfer/global/show_in_store';

	public function sendFile($localFile, $remoteFile, $configPath, $store=null)
	{
		try {
			$protocol = $this->getProtocolModel($configPath, $store);
			$protocol->sendFile($localFile, $remoteFile);
		} catch (Exception $e) {
			Mage::log("filetransfer send error:". $e->getMessage());
		}
	}

	public function getFile($remoteFile, $localFile, $configPath, $store=null)
	{
		try {
			$protocol = $this->getProtocolModel($configPath, $store);
			$protocol->getFile($localFile, $remoteFile);
		} catch (Exception $e) {
			Mage::log("filetransfer get error:". $e->getMessage());
		}
	}

	/**
	 * returns the model for the configured protocol.
	 * */
	public function getProtocolModel($configPath, $store=null)
	{
		$protocol = Mage::getStoreConfig(
			sprintf('%s/filetransfer_protocol', $configPath),
			$store
		);
		if (!$protocol) {
			$protocol = $this->getDefaultProtocol();
		}
		$config = Mage::getModel(
			'filetransfer/protocol_config',
			array('store'=>$store, 'config_path'=>$configPath)
		);
		try {
			return Mage::getModel(
				'filetransfer/protocol_'.$protocol,
				array('config' => $config)
			);
		} catch (Exception $e) {
			Mage::throwException(
				"Unable to get the protocol model where protocol='$protocol'."
			);
		}
	}

	/**
	 * returns the default protocol to use when sending files.
	 * @param Mage_Core_Model_Store
	 * @return string
	 * */
	public function getDefaultProtocol($store=null)
	{
		return Mage::getStoreConfig(self::GLOBAL_DEFAULT_PROTOCOL, $store);
	}
	/**
	 * default initial sort order for dynamically inserted config fields.
	 * @param Mage_Core_Model_Store
	 * @return string
	 * */
	public function getGlobalSortOrder($store=null)
	{
		return Mage::getStoreConfig(self::GLOBAL_SORT_ORDER, $store);
	}
	/**
	 * default show_in_default value for dynamically inserted config fields.
	 * @param Mage_Core_Model_Store
	 * @return string
	 * */
	public function getGlobalShowInDefault($store=null)
	{
		return Mage::getStoreConfig(self::GLOBAL_SHOW_IN_DEFAULT, $store);
	}
	/**
	 * default show_in_website value for dynamically inserted config fields.
	 * @param Mage_Core_Model_Store
	 * @return string
	 * */
	public function getGlobalShowInWebsite($store=null)
	{
		return Mage::getStoreConfig(self::GLOBAL_SHOW_IN_WEBSITE, $store);
	}
	/**
	 * default show_in_store value for dynamically inserted config fields.
	 * @param Mage_Core_Model_Store
	 * @return string
	 * */
	public function getGlobalShowInStore($store=null)
	{
		return Mage::getStoreConfig(self::GLOBAL_SHOW_IN_STORE, $store);
	}
}