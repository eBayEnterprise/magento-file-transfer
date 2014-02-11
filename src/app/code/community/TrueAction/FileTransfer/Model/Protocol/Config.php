<?php
/*
TrueAction_FileTransfer_Model_Config_Ftp

concrete configuration generator for the ftp protocol.
 */
class TrueAction_FileTransfer_Model_Protocol_Config
	extends TrueAction_ActiveConfig_Model_Config_Abstract
{
	const DEFAULT_FIELD_TEMPLATE = 'filetransfer/base_fields/template';
	protected $_fieldMap = array(
		'filetransfer_%s_username'    => 'username',
		'filetransfer_%s_password'    => 'password',
		'filetransfer_%s_host'        => 'host',
		'filetransfer_%s_port'        => 'port',
		'filetransfer_%s_remote_path' => 'remote_path',
	);

	/**
	 * loads the dynamic config and does some validation checks.
	 * @throws TrueAction_FileTransfer_Exception_Configuration If the protocol code is invalid
	 */
	protected function _construct()
	{
		// create magic getter/setters for each field
		$this->loadMappedFields($this->_fieldMap);
		$this->_validateProtocolCode();
	}

	/**
	 * validate the protocol code
	 * @throws TrueAction_FileTransfer_Exception_Configuration If the protocol doesnt match a list of implemented models
	 * @return self
	 */
	public function _validateProtocolCode()
	{
		$codes = Mage::helper('filetransfer')->getProtocolCodes();
		$isProtocolValid = in_array(
			(string) $this->getProtocolCode(),
			$codes,
			true
		);
		if (!$isProtocolValid) {
			throw new TrueAction_FileTransfer_Exception_Configuration(
				sprintf(
					'FileTransfer Config Error: Invalid Protocol Code "%s"',
					$this->getProtocolCode()
				)
			);
		}
		return $this;
	}
	/**
	 * Get the unencrypted password
	 *
	 * @return string
	 */
	public function getPassword()
	{
		return Mage::helper('core')->decrypt($this->getData('password'));
	}
	/**
	 * Encrypt the passed value and set the field 'password' to it.
	 *
	 * @see Varien_Object::setData
	 */
	public function setPassword($pass)
	{
		return $this->setData('password', Mage::helper('core')->encrypt($pass));
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
		$defaultFlag = isset($moduleSpec->$showInDefault) && (string) $moduleSpec->$showInDefault !== '' ?
			(string) $moduleSpec->$showInDefault : $helper->getGlobalShowInDefault();
		$websiteFlag = isset($moduleSpec->$showInWebsite) && (string) $moduleSpec->$showInWebsite !== '' ?
			(string) $moduleSpec->$showInWebsite : $helper->getGlobalShowInWebsite();
		$storeFlag = isset($moduleSpec->$showInStore) && (string) $moduleSpec->$showInStore !== '' ?
			(string) $moduleSpec->$showInStore : $helper->getGlobalShowInStore();

		$increment = 0;
		$fields = $this->getBaseFields();
		foreach ($fields->getNode()->children() as $fieldName => $fieldNode) {
			$fields->setNode($fieldName . '/sort_order', $sortOrder + $increment++);
			// compute field level display flags
			$fieldDefaultFlag = isset($fieldNode->$showInDefault) && (string) $fieldNode->$showInDefault !== '' ?
				(string) $fieldNode->$showInDefault :
				$defaultFlag;
			$fieldWebsiteFlag = isset($fieldNode->$showInWebsite) && (string) $fieldNode->$showInWebsite !== '' ?
				(string) $fieldNode->$showInWebsite :
				$websiteFlag;
			$fieldStoreFlag = isset($fieldNode->$showInStore) && (string) $fieldNode->$showInStore !== '' ?
				(string) $fieldNode->$showInStore :
				$storeFlag;
			$fields->setNode($fieldName . '/show_in_default', $fieldDefaultFlag);
			$fields->setNode($fieldName . '/show_in_website', $fieldWebsiteFlag);
			$fields->setNode($fieldName . '/show_in_store', $fieldStoreFlag);
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
		return new Varien_Simplexml_Config(
			str_replace('%s', $this->getProtocolCode(), Mage::getStoreConfig(self::DEFAULT_FIELD_TEMPLATE))
		);
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
