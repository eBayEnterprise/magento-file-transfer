<?php
/*
TrueAction_FileTransfer_Model_Config_Ftp

concrete configuration generator for the ftp protocol.
 */
class TrueAction_FileTransfer_Model_Config_Ftp
	extends TrueAction_ActiveConfig_Model_Config_Abstract
{
	private $_fieldPrefix = '';

	public function __construct()
	{
		$this->_fieldPrefix = sprintf('%s_%s', 'filetransfer', 'ftp');
	}

	/**
	 * returns the field prefix for each field
	 * @return string
	 * */
	public function getFieldPrefix()
	{
		return $this->_fieldPrefix;
	}

	/**
	 * generates the xml nodes that comprise a set of configuration fields
	 * in the magento system config.
	 * @param Varien_Simplexml_Element $importOptions
	 * @return Varien_Simplexml_Config
	 * */
	public function getConfig($importOptions) {
		$fields = new Varien_Simplexml_Config();
		$fields->loadString("
			<fields>
			<{$this->_fieldPrefix}_username translate=\"label\">
				<label>Username</label>
				<frontend_type>text</frontend_type>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
			</{$this->_fieldPrefix}_username>
			<{$this->_fieldPrefix}_password translate=\"label\">
				<label>Password</label>
				<frontend_type>obscure</frontend_type>
				<backend_model>adminhtml/system_config_backend_encrypted</backend_model>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
			</{$this->_fieldPrefix}_password>
			<{$this->_fieldPrefix}_host translate=\"label\">
				<label>Remote Host</label>
				<frontend_type>text</frontend_type>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
			</{$this->_fieldPrefix}_host>
			<{$this->_fieldPrefix}_port translate=\"label\">
				<label>Remote Port</label>
				<frontend_type>text</frontend_type>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
			</{$this->_fieldPrefix}_port>
			<{$this->_fieldPrefix}_remote_path translate=\"label\">
				<label>Remote Path</label>
				<frontend_type>text</frontend_type>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
			</{$this->_fieldPrefix}_remote_path>
			</fields>
		");
		return $fields;
	}
}
