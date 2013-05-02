<?php
/*
TrueAction_ActiveConfig_Model_Generator

Interface class for all configuration generators
 */
class TrueAction_FileTransfer_Model_Config_Ftp
	extends TrueAction_ActiveConfig_Model_Config_Abstract
{
	/**
	 * generates the xml nodes that comprise a set of configuration fields
	 * in the magento system config
	 * */
	public function getConfig($importOptions) {
		$fields = new Varien_SimpleXml_Config();
		$fields->loadString('
			<fields>
			<ftp_username translate="label">
				<label>Username</label>
				<frontend_type>text</frontend_type>
				<sort_order>1001</sort_order>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
			</ftp_username>
			<ftp_password translate="label">
				<label>Password</label>
				<frontend_type>obscure</frontend_type>
				<backend_model>adminhtml/system_config_backend_encrypted</backend_model>
				<sort_order>1002</sort_order>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
			</ftp_password>
			<ftp_host translate="label">
				<label>Remote Host</label>
				<frontend_type>text</frontend_type>
				<sort_order>1003</sort_order>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
			</ftp_host>
			<ftp_port translate="label">
				<label>Remote Port</label>
				<frontend_type>text</frontend_type>
				<sort_order>1004</sort_order>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
			</ftp_port>
			</fields>
		');
	}
}
