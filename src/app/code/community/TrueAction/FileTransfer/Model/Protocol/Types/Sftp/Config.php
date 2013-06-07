<?php
/*
TrueAction_FileTransfer_Model_Protocol_Types_Sftp_Config

concrete configuration generator for the ftp protocol.
 */
class TrueAction_FileTransfer_Model_Protocol_Types_Sftp_Config
	extends TrueAction_FileTransfer_Model_Protocol_Config
{
	/**
	 * initialize the config with sftp specific data.
	 */
	protected function _construct()
	{
		$this->_fieldMap['filetransfer_sftp_ssh_prv_key'] = 'private_key';
		$this->_fieldMap['filetransfer_sftp_ssh_pub_key'] = 'public_key';
		parent::_construct();
	}

	/**
	 * generate the normal filetransfer fields along with the ssh key field.
	 * @param Varien_Simplexml_Element $importOptions
	 * @return Varien_Simplexml_Config
	 * */
	public function _getBaseFields() {
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
			<filetransfer_{$protocol}_ssh_prv_key translate=\"label\">
				<label>SSH Private Key</label>
				<frontend_type>text</frontend_type>
				<depends><filetransfer_protocol>{$protocol}</filetransfer_protocol></depends>
			</filetransfer_{$protocol}_ssh_prv_key>
			<filetransfer_{$protocol}_ssh_pub_key translate=\"label\">
				<label>SSH Public Key</label>
				<frontend_type>text</frontend_type>
				<depends><filetransfer_protocol>{$protocol}</filetransfer_protocol></depends>
			</filetransfer_{$protocol}_ssh_pub_key>
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
	}}
