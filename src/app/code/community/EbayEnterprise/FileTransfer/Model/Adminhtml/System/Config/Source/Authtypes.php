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

class EbayEnterprise_FileTransfer_Model_Adminhtml_System_Config_Source_Authtypes
{
	public function toOptionArray()
	{
		$helper = Mage::helper('filetransfer');
		return array(
			array('value' => 'password', 'label' => $helper->__('Password')),
			array('value' => 'pub_key',  'label' => $helper->__('Public Key'))
		);
	}

	public function toArray()
	{
		$helper = Mage::helper('filetransfer');
		return array(
			'password' => $helper->__('Password'),
			'pub_key'  => $helper->__('Public Key')
		);
	}
}
