<?php
/*
TrueAction_FileTransfer_Model_Config_Ftp

concrete configuration generator for the ftp protocol.
 */
class TrueAction_FileTransfer_Model_Protocol_Config
	extends TrueAction_ActiveConfig_Model_Config_Abstract
{
	private $_fieldPrefix = 'filetransfer';
	private $_fieldMap    = array();

	/**
	 * returns the field prefix for each field
	 * @return string
	 * */
	public function loadMappedFields($map)
	{
		$this->_fieldMap = $map;
		foreach ($map as $configField => $magicField) {
			$this->_loadFieldAsMagic($configField, $magicField);
		}
	}

	public function generateFields($moduleSpec)
	{
		Mage::log("generateFields");
		$helper = Mage::helper('filetransfer');

		$sortOrder = isset($moduleSpec->sort_order) ?
			(int)$moduleSpec->sort_order : $helper->getGlobalSortOrder();
		$defaultFlag = isset($moduleSpec->show_in_default) ?
			(string)$moduleSpec->show_in_default : $helper->getGlobalShowInDefault();
		$websiteFlag = isset($moduleSpec->show_in_website) ?
			(string)$moduleSpec->show_in_website : $helper->getGlobalShowInWebsite();
		$storeFlag = isset($moduleSpec->show_in_store) ?
			(string)$moduleSpec->show_in_store : $helper->getGlobalShowInStore();

		$protocolModel = $helper->getProtocolModel($this->getConfigPath());
		$fields = $this->_getBaseFields($protocolModel->getCode());

		// TODO: ADD FEATURE SPECIFIC OPTIONS
		$increment = 0;
		foreach ($fields->getNode()->children() as $fieldName => $fieldNode) {
			$fields->setNode($fieldName.'/sort_order', $sortOrder + $increment++);
			$fields->setNode($fieldName.'/show_in_default', $defaultFlag);
			$fields->setNode($fieldName.'/show_in_website', $websiteFlag);
			$fields->setNode($fieldName.'/show_in_store', $storeFlag);
		}

		return $fields;
	}

	/**
	 * generates the xml nodes that comprise a set of configuration fields
	 * in the magento system config.
	 * @param Varien_Simplexml_Element $importOptions
	 * @return Varien_Simplexml_Config
	 * */
	public function _getBaseFields($protocol) {
		$fields = new Varien_Simplexml_Config("
		<fields>
			<filetransfer_protocol translate=\"label\">
				<label>Protocol</label>
				<frontend_type>select</frontend_type>
				<source_model>filetransfer/adminhtml_system_config_source_protocols</source_model>
			</filetransfer_protocol>
			<filetransfer_{$protocol}_username translate=\"label\">
				<label>Username</label>
				<frontend_type>text</frontend_type>
			</filetransfer_{$protocol}_username>
			<filetransfer_{$protocol}_password translate=\"label\">
				<label>Password</label>
				<frontend_type>obscure</frontend_type>
				<backend_model>adminhtml/system_config_backend_encrypted</backend_model>
			</filetransfer_{$protocol}_password>
			<filetransfer_{$protocol}_host translate=\"label\">
				<label>Remote Host</label>
				<frontend_type>text</frontend_type>
			</filetransfer_{$protocol}_host>
			<filetransfer_{$protocol}_port translate=\"label\">
				<label>Remote Port</label>
				<frontend_type>text</frontend_type>
			</filetransfer_{$protocol}_port>
			<filetransfer_{$protocol}_remote_path translate=\"label\">
				<label>Remote Path</label>
				<frontend_type>text</frontend_type>
			</filetransfer_{$protocol}_remote_path>
		</fields>
		");
		return $fields;
	}
}