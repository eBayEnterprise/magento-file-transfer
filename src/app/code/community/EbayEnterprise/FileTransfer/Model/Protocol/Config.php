<?php
/*
EbayEnterprise_FileTransfer_Model_Config_Ftp

concrete configuration generator for the ftp protocol.
 */
class EbayEnterprise_FileTransfer_Model_Protocol_Config
	extends EbayEnterprise_ActiveConfig_Model_Config_Abstract
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
	 * @throws EbayEnterprise_FileTransfer_Exception_Configuration If the protocol code is invalid
	 */
	protected function _construct()
	{
		// create magic getter/setters for each field
		$this->loadMappedFields($this->_fieldMap);
		$this->_validateProtocolCode();
	}

	/**
	 * validate the protocol code
	 * @throws EbayEnterprise_FileTransfer_Exception_Configuration If the protocol doesnt match a list of implemented models
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
			throw new EbayEnterprise_FileTransfer_Exception_Configuration(
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
	 * Search for a value in a series of objects. Stop when the first one is found.
	 *
	 * @param array $objs The objects to look through.
	 * @param string $name The name to search for.
	 * @return int The first value found or the value at the helper.
	 */
	protected function _searchForFields(array $objs, $name)
	{
		foreach ($objs as $o) {
			if (isset($o->$name) && trim($o->$name) !== '') {
				return (int) $o->$name;
			}
		}
		return (int) call_user_func(array(Mage::helper('filetransfer'), 'getGlobal' . uc_words($name, '')));
	}
	/**
	 * Return config xml generated fields
	 */
	public function generateFields($moduleSpec)
	{
		$displayLevels = array('show_in_default', 'show_in_website', 'show_in_store');
		$sortOrder = $this->_searchForFields(array($moduleSpec), 'sort_order');
		$fields = $this->getBaseFields();
		foreach ($fields->getNode()->children() as $fieldName => $fieldNode) {
			$fields->setNode("$fieldName/sort_order", $sortOrder++);
			// compute field level display flags
			$objs = array($fieldNode, $moduleSpec);
			foreach ($displayLevels as $name) {
				$fields->setNode("$fieldName/$name", $this->_searchForFields($objs, $name));
			}
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
