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

class EbayEnterprise_FileTransfer_Model_Adminhtml_System_Config_Source_Protocols
{
	public function toOptionArray()
	{
		$helper = Mage::helper('filetransfer');
		$list = array();
		$protocolCodes = EbayEnterprise_FileTransfer_Model_Protocol_Abstract::getCodes();
		foreach ($protocolCodes as $code) {
			$model = Mage::getModel('filetransfer/protocol_types_' . $code);
			$list[] = array(
				'value' => $code,
				'label' => $helper->__($model->getName())
			);
		}
		return $list;
	}

	public function toArray()
	{
		$helper = Mage::helper('filetransfer');
		$list = array();
		$protocolCodes = EbayEnterprise_FileTransfer_Model_Protocol_Abstract::getCodes();
		foreach ($protocolCodes as $code) {
			$model = Mage::getModel('filetransfer/protocol_types_' . $code);
			$list[] = array($code => $helper->__($model->getName()));
		}
		return $list;
	}
}
