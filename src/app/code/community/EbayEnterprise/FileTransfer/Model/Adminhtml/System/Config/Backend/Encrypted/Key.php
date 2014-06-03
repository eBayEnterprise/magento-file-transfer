<?php
/**
 * Copyright (c) 2013-2014 eBay Enterprise, Inc.
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @copyright   Copyright (c) 2013-2014 eBay Enterprise, Inc. (http://www.ebayenterprise.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * A class that ensures Private Key is valid, and only its Public Key is ever displayed
 * once the key is entered.
 */
class EbayEnterprise_FileTransfer_Model_Adminhtml_System_Config_Backend_Encrypted_Key
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
	 * If the new key is valid, our parent processes the encryption, issue a Notice.
	 * If the new key is not valid, and there is no previous value, issue an Error.
	 * If the new key is not valid, and there is a previous value, keep the previous value and issue a Warning.
	 * @return self
	 */
	public function _beforeSave()
	{
		$sess = Mage::getSingleton($this::SESSION_KEY);
		$newValue = $this->getValue();
		if (!empty($newValue)) {
			if (openssl_pkey_get_private($newValue)) {
				$this->_dataSaveAllowed = true;
				$sess->addNotice('New Private Key successfully installed.');
			} else {
				$this->_dataSaveAllowed = false;
				$oldValue = $this->getOldValue();
				if (!empty($oldValue)) {
					$sess->addWarning('An Invalid Private Key was Specified. The previous value has been retained.');
				} else {
					// Our new key is invalid, and we had no sensible old value. Error noisily.
					$sess->addError('An Invalid Private Key was Specified. Exchange Platform Batch Feeds will not work until this is corrected.');
				}
			}
			parent::_beforeSave(); // Our parent does all the right things to save it encrypted.
		} else {
			$this->_dataSaveAllowed = false;
		}
		return $this;
	}
}
