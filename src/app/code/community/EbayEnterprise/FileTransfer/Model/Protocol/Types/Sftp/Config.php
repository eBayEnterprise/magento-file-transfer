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

class EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp_Config extends EbayEnterprise_FileTransfer_Model_Protocol_Config
{
	const CONFIG_TEMPLATE = 'filetransfer/sftp_fields/template';
	/**
	 * initialize the config with sftp specific data.
	 */
	protected function _construct()
	{
		$this->setProtocolCode('sftp');
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
	 * Decrypt the private key when retrieving it from the database.
	 * @return string
	 */
	public function getPrivateKey()
	{
		return Mage::helper('core')->decrypt($this->getData('private_key'));
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
