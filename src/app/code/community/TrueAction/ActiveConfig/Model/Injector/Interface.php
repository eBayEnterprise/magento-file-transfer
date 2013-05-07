<?php

/*
 * interface for classes that insert new nodes into the adminhtml system
 * configuration using a specified attachment point.
 */
interface TrueAction_ActiveConfig_Model_Injector_Interface {

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
