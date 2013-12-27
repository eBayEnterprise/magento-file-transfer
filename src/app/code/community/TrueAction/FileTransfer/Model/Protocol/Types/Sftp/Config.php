<?php
class TrueAction_FileTransfer_Model_Protocol_Types_Sftp_Config extends TrueAction_FileTransfer_Model_Protocol_Config
{
	const CONFIG_TEMPLATE = 'filetransfer/sftp_fields/template';
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
		return new Varien_Simplexml_Config(sprintf(Mage::getStoreConfig(self::CONFIG_TEMPLATE), $this->getProtocolCode()));
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
