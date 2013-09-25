<?php
/*
TrueAction_FileTransfer_Model_Config_Ftp

concrete configuration generator for the ftp protocol.
 */
class TrueAction_FileTransfer_Model_Protocol_Config
	extends TrueAction_ActiveConfig_Model_Config_Abstract
{
	protected $_fieldMap = array(
		'filetransfer_%s_username'    => 'username',
		'filetransfer_%s_password'    => 'password',
		'filetransfer_%s_host'        => 'host',
		'filetransfer_%s_port'        => 'port',
		'filetransfer_%s_remote_path' => 'remote_path',
	);

	/**
	 * loads the dynamic config and does some validation checks.
	 * */
	protected function _construct()
	{
		// create magic getter/setters for each field
		$this->loadMappedFields($this->_fieldMap);

		$isProtocolValid = array_search(
			$this->getProtocolCode(),
			Mage::helper('filetransfer')->getProtocolCodes()
		);
		if ($isProtocolValid === false) {
			try {
				Mage::throwException(
					sprintf(
						'FileTransfer Config Error: Invalid Protocol Code "%s"',
						$this->getProtocolCode()
					)
				);
			} catch (Exception $e) {
				Mage::logException($e);
			}
		}
	}

	/**
	 * get the unencrypted password
	 * @return string
	 */
	public function getPassword()
	{
		return Mage::helper('core')->decrypt($this->getData('password'));
	}

	/**
	 * set and encrypt the password
	 */
	public function setPassword()
	{
		$this->setData(
			Mage::helper('core')->encrypt($this->getData('password'))
		);
		return $this;
	}

	/**
	 * loads the config data.
	 */
	public function loadMappedFields($map)
	{
		$this->_fieldMap = $map;
		$protocolCode = $this->getProtocolCode();
		foreach ($map as $configField => $magicField) {
			$this->_loadFieldAsMagic(
				sprintf($configField, $protocolCode),
				$magicField
			);
		}
	}

	/**
	 * Returns generated fields
 	 *
	 * @todo: Add feature specific options
	 */
	public function generateFields($moduleSpec)
	{
		$helper = Mage::helper('filetransfer');

		// This is a rather ham-handed way of avoiding complaints about invalid Camel Case variable names:
		$sortOrder     = 'sort_order';
		$showInDefault = 'show_in_default';
		$showInWebsite = 'show_in_website';
		$showInStore   = 'show_in_store';

		$sortOrder = isset($moduleSpec->$sortOrder) ?
			(int) $moduleSpec->$sortOrder : $helper->getGlobalSortOrder();
		$defaultFlag = isset($moduleSpec->$showInDefault) ?
			(string) $moduleSpec->$showInDefault : $helper->getGlobalShowInDefault();
		$websiteFlag = isset($moduleSpec->$showInWebsite) ?
			(string) $moduleSpec->$showInWebsite : $helper->getGlobalShowInWebsite();
		$storeFlag = isset($moduleSpec->$showInStore) ?
			(string) $moduleSpec->$showInStore : $helper->getGlobalShowInStore();

		$fields = $this->getBaseFields();

		$increment = 0;
		foreach ($fields->getNode()->children() as $fieldName => $fieldNode) {
			$fields->setNode($fieldName . '/sort_order', $sortOrder + $increment++);
			$fields->setNode($fieldName . '/show_in_default', $defaultFlag);
			$fields->setNode($fieldName . '/show_in_website', $websiteFlag);
			$fields->setNode($fieldName . '/show_in_store', $storeFlag);
		}
		return $fields;
	}

	/**
	 * generates the xml nodes that comprise a set of configuration fields
	 * in the magento system config.
	 * @param Varien_Simplexml_Element $importOptions
	 * @return Varien_Simplexml_Config
	 * */
	public function getBaseFields()
	{
		$protocol = $this->getProtocolCode();
		$fields   = new Varien_Simplexml_Config("
		<fields>
			<filetransfer_protocol translate=\"label\">
				<label>Protocol</label>
				<frontend_type>select</frontend_type>
				<source_model>filetransfer/adminhtml_system_config_source_protocols</source_model>
			</filetransfer_protocol>
			<filetransfer_{$protocol}_username translate=\"label\">
				<label>Username</label>
				<frontend_type>text</frontend_type>
				<depends><filetransfer_protocol>{$protocol}</filetransfer_protocol></depends>
			</filetransfer_{$protocol}_username>
			<filetransfer_{$protocol}_password translate=\"label\">
				<label>Password</label>
				<frontend_type>obscure</frontend_type>
				<backend_model>adminhtml/system_config_backend_encrypted</backend_model>
				<depends><filetransfer_protocol>{$protocol}</filetransfer_protocol></depends>
			</filetransfer_{$protocol}_password>
			<filetransfer_{$protocol}_host translate=\"label\">
				<label>Remote Host</label>
				<frontend_type>text</frontend_type>
				<depends><filetransfer_protocol>{$protocol}</filetransfer_protocol></depends>
			</filetransfer_{$protocol}_host>
			<filetransfer_{$protocol}_port translate=\"label\">
				<label>Remote Port</label>
				<frontend_type>text</frontend_type>
				<depends><filetransfer_protocol>{$protocol}</filetransfer_protocol></depends>
			</filetransfer_{$protocol}_port>
			<filetransfer_{$protocol}_remote_path translate=\"label\">
				<label>Remote Path</label>
				<frontend_type>text</frontend_type>
				<depends><filetransfer_protocol>{$protocol}</filetransfer_protocol></depends>
			</filetransfer_{$protocol}_remote_path>
		</fields>
		");
		return $fields;
	}

	/**
	 * return a URL using the configured values.
	 * @param  bool $includePath
	 * @return string
	 */
	public function getUrl($includePath=false)
	{
		$url = sprintf(
			'%s://%s%s%s/%s',
			$this->getProtocolCode(),
			$this->getUser() ? $this->getUser() . '@' : '',
			$this->getHost(),
			$this->getPort() ? ':' . $this->getPort() : '',
			$includePath && $this->hasRemotePath() ? $this->getRemotePath() : ''
		);
		return $url;
	}
}
