<?php
class TrueAction_FileTransfer_Model_Protocol_Types_Sftp_Config extends TrueAction_FileTransfer_Model_Protocol_Config
{
	const CONFIG_TEMPLATE = '<fields>
		<filetransfer_protocol translate="label">
			<label>Protocol</label>
			<frontend_type>select</frontend_type>
			<source_model>filetransfer/adminhtml_system_config_source_protocols</source_model>
		</filetransfer_protocol>
		<filetransfer_sftp_auth_type translate="label">
			<label>Authentication Method</label>
			<frontend_type>select</frontend_type>
			<source_model>filetransfer/adminhtml_system_config_source_Authtypes</source_model>
			<depends><filetransfer_protocol>sftp</filetransfer_protocol></depends>
		</filetransfer_sftp_auth_type>
		<filetransfer_%1$s_username translate="label">
			<label>Username</label>
			<frontend_type>text</frontend_type>
			<depends><filetransfer_protocol>%1$s</filetransfer_protocol></depends>
		</filetransfer_%1$s_username>
		<filetransfer_%1$s_password translate="label">
			<label>Password</label>
			<validate>required-entry</validate>
			<frontend_type>obscure</frontend_type>
			<backend_model>adminhtml/system_config_backend_encrypted</backend_model>
			<depends>
				<filetransfer_protocol>%1$s</filetransfer_protocol>
				<filetransfer_sftp_auth_type>password</filetransfer_sftp_auth_type>
			</depends>
		</filetransfer_%1$s_password>
		<filetransfer_%1$s_ssh_prv_key translate="label">
			<label>Private Key</label>
			<validate>required-entry</validate>
			<frontend_type>obscure</frontend_type>
			<backend_model>adminhtml/system_config_backend_encrypted</backend_model>
			<depends>
				<filetransfer_protocol>sftp</filetransfer_protocol>
				<filetransfer_sftp_auth_type>pub_key</filetransfer_sftp_auth_type>
			</depends>
		</filetransfer_%1$s_ssh_prv_key>
		<filetransfer_%1$s_host translate="label">
			<label>Remote Host</label>
			<frontend_type>text</frontend_type>
			<depends><filetransfer_protocol>%1$s</filetransfer_protocol></depends>
		</filetransfer_%1$s_host>
		<filetransfer_%1$s_port translate="label">
			<label>Remote Port</label>
			<frontend_type>text</frontend_type>
			<depends><filetransfer_protocol>%1$s</filetransfer_protocol></depends>
		</filetransfer_%1$s_port>
		<filetransfer_%1$s_remote_path translate="label">
			<label>Remote Path</label>
			<frontend_type>text</frontend_type>
			<depends><filetransfer_protocol>%1$s</filetransfer_protocol></depends>
		</filetransfer_%1$s_remote_path>
	</fields>';
	/**
	 * initialize the config with sftp specific data.
	 */
	protected function _construct()
	{
		$this->_fieldMap['filetransfer_sftp_ssh_prv_key'] = 'private_key';
		$this->_fieldMap['filetransfer_sftp_auth_type']   = 'auth_type';
		parent::_construct();
	}
	/**
	 * Generate the normal filetransfer fields along with the ssh key field.
	 * @param Varien_Simplexml_Element $importOptions
	 * @return Varien_Simplexml_Config
	 */
	public function getBaseFields()
	{
		return new Varien_Simplexml_Config(sprintf(self::CONFIG_TEMPLATE, $this->getProtocolCode()));
	}
	/**
	 * Fix the key formatting which may have been lost when saving the key in the admin.
	 * This is a hacky fix for the way the key is currently loaded into the admin. Ideally,
	 * this won't be necessary and can eventually be removed.
	 *
	 * @param string $key
	 * @return string
	 */
	protected function _reformatKey($key)
	{
		$header = '-----BEGIN RSA PRIVATE KEY-----';
		$footer = '-----END RSA PRIVATE KEY-----';
		$key = trim(substr($key, strlen($header), (strlen($footer) * -1)));
		$i = 1;
		$c = 64;
		while ($c < strlen($key)) {
			$key[$c] = "\n";
			++$i;
			$c = 64 * $i + ($i - 1);
		}
		return $header . PHP_EOL . $key . PHP_EOL . $footer;
	}
	/**
	 * Decrypt the private key when retrieving it from the database.
	 * @return string
	 */
	public function getPrivateKey()
	{
		return $this->_reformatKey(Mage::helper('core')->decrypt($this->getData('private_key')));
	}
	/**
	 * Encrypt the private key when setting it.
	 * @param string $key
	 * @return self
	 */
	public function setPrivateKey($key)
	{
		return $this->setData('private_key', Mage::helper('core')->encrypt($key));
	}
}
