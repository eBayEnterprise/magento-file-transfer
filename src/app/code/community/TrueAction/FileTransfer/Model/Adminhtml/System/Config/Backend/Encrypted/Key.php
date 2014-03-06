<?php
/**
 * A class that ensures Private Key is valid, and only its Public Key is ever displayed
 * once the key is entered.
 */
class TrueAction_FileTransfer_Model_Adminhtml_System_Config_Backend_Encrypted_Key
	extends Mage_Adminhtml_Model_System_Config_Backend_Encrypted
{
	const SESSION_KEY = 'adminhtml/session';
	/**
	 * Return public key from private key, if applicable.
	 * return string
	 */
	protected function _getPublicKeyFromPrivateKey($decryptedValue)
	{
		$publicKey  = '';
		$privateKey = openssl_pkey_get_private($decryptedValue);
		if ($privateKey) {
			$details   = openssl_pkey_get_details($privateKey);
			$publicKey = $details['key'];
		}
		return $publicKey;
	}

	/**
	 * Checks that the decrypted value we have stored is actually a valid key.
	 * If it's a valid key, sets the Private Key field to display the Public Key.
	 * @return self
	 */
	public function _afterLoad()
	{
		parent::_afterLoad();
		$publicDisplay  = '';
		$decryptedValue = $this->getValue();
		if (!empty($decryptedValue)) {
			$publicDisplay = $this->_getPublicKeyFromPrivateKey($decryptedValue);
		}
		$this->setValue($publicDisplay);
		return $this;
	}

	/**
	 * Checks to see if we have a new and valid private key.
	 * If it's a valid key, we let our parent process the encryption.
	 * If it's not a valid key (and not the Security Mask), we restore the previous value, and issue a warning.
	 * @return self
	 */
	public function _beforeSave()
	{
		$publicKey = '';
		$oldValue  = $this->getOldValue();
		if(!empty($oldValue)) {
			$publicKey = $this->_getPublicKeyFromPrivateKey($oldValue);
		}
		$newValue = $this->getValue();
		if (!empty($newValue) && $newValue !== $publicKey) {
			if (openssl_pkey_get_private($newValue)) {
				Mage::getSingleton($this::SESSION_KEY)->addNotice('New Private Key successfully installed.');
			} else {
				if( !empty($oldValue) && openssl_pkey_get_private($oldValue)) {
					$this->setValue($oldValue);
					Mage::getSingleton($this::SESSION_KEY)
						->addWarning('An Invalid Private Key was Specified. The previous value has been retained.');
				} else {
					// Our new key is invalid, and we had no sensible old value. Error noisily.
					$this->setValue('');
					Mage::getSingleton($this::SESSION_KEY)
						->addError('An Invalid Private Key was Specified. Exchange Platform Batch Feeds will not work until this is corrected.');
				}
			}
			parent::_beforeSave(); // Our parent does all the right things to save it encrypted.
		}
		return $this;
	}
}
