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


/*
 * interface for classes that insert new nodes into the adminhtml system
 * configuration using a specified attachment point.
 */
interface EbayEnterprise_ActiveConfig_Model_Injector_Interface {

	/**
	 * insert the xml nodes that comprise a set of configuration fields
	 * into the magento system config.
	 * @param Varien_Simplexml_Config $config
	 * */
	public function insertConfig($config);


	/**
	 * specify the attachment point.
	 * @param Varien_Simplexml_Element $attachmentPoint
	 * */
	public function setAttachmentPoint($attachmentPoint);
}
