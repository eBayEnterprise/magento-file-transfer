<?php
class EbayEnterprise_FileTransfer_Helper_Data extends Mage_Core_Helper_Abstract
{
	protected $_configErrorClass = 'Exception';

	const GLOBAL_DEFAULT_PROTOCOL = 'filetransfer/global/default_protocol';
	const GLOBAL_SORT_ORDER       = 'filetransfer/global/sort_order';
	const GLOBAL_SHOW_IN_DEFAULT  = 'filetransfer/global/show_in_default';
	const GLOBAL_SHOW_IN_WEBSITE  = 'filetransfer/global/show_in_website';
	const GLOBAL_SHOW_IN_STORE    = 'filetransfer/global/show_in_store';

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
		return EbayEnterprise_FileTransfer_Model_Protocol_Abstract::getCodes();
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
