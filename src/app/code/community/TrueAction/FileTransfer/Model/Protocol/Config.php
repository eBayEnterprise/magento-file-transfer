<?php
/*
TrueAction_FileTransfer_Model_Config_Ftp

concrete configuration generator for the ftp protocol.
 */
class TrueAction_FileTransfer_Model_Config_Ftp
	extends TrueAction_ActiveConfig_Model_Config_Abstract
{
	private $_fieldPrefix = 'filetransfer_ftp';

	/**
	 * returns the field prefix for each field
	 * @return string
	 * */
	public function getFieldPrefix()
	{
		return $this->_fieldPrefix;
	}

	public function get

	public function setImportOptions($importOptions)
	{
		$helper = Mage::helper('filetransfer');
		$this->_globalSortOrder = $importOptions->sort_order ?
			$importOptions->sort_order : $helper->getGlobalSortOrder();
		$this->_globalStoreFlag = $importOptions->show_in_store ?
			$importOptions->show_in_store : $helper->getGlobalShowInStore();
		$this->_globalWebsiteFlag = $importOptions->show_in_website ?
			$importOptions->show_in_website : $helper->getGlobalShowInWebsite();
		$this->_globalDefaultFlag = $importOptions->show_in_default ?
			$importOptions->show_in_default : $helper->getGlobalShowInDefault();
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
			<ftransfer_protocol translate=\"label\">
				<label>Protocol</label>
				<frontend_type>text</frontend_type>
				<sort_order>99</sort_order>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
			</ftransfer_protocol>
			<{$this->_fieldPrefix}_username translate=\"label\">
				<label>Username</label>
				<frontend_type>text</frontend_type>
				<sort_order>100</sort_order>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
			</{$this->_fieldPrefix}_username>
			<{$this->_fieldPrefix}_password translate=\"label\">
				<label>Password</label>
				<frontend_type>obscure</frontend_type>
				<backend_model>adminhtml/system_config_backend_encrypted</backend_model>
				<sort_order>101</sort_order>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
			</{$this->_fieldPrefix}_password>
			<{$this->_fieldPrefix}_host translate=\"label\">
				<label>Remote Host</label>
				<frontend_type>text</frontend_type>
				<sort_order>102</sort_order>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
			</{$this->_fieldPrefix}_host>
			<{$this->_fieldPrefix}_port translate=\"label\">
				<label>Remote Port</label>
				<frontend_type>text</frontend_type>
				<sort_order>103</sort_order>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
			</{$this->_fieldPrefix}_port>
			<{$this->_fieldPrefix}_remote_path translate=\"label\">
				<label>Remote Path</label>
				<frontend_type>text</frontend_type>
				<sort_order>104</sort_order>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
			</{$this->_fieldPrefix}_remote_path>
			</fields>
		");
		return $fields;
	}
}
