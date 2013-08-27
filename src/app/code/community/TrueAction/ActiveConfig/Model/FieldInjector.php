<?php
/**
 * inserts new nodes into the configuration using a specified
 * attachement point.
 * */
class TrueAction_ActiveConfig_Model_FieldInjector
	implements TrueAction_ActiveConfig_Model_Injector_Interface
{
	// group node the injector is attached to.
	private $_groupNode = null;

	/**
	 * create the injector and optionally set a group node as the attachment
	 * point.
	 * @param Varien_Simplexml_Element $groupNode
	 * */
	public function __construct($groupNode = null)
	{
		$this->_groupNode = $groupNode;
	}

	/**
	 * insert the xml nodes that comprise a set of configuration fields
	 * into the attached group in the magento system config.
	 * @param Varien_Simplexml_Config $fieldsConfig
	 * */
	public function insertConfig($fieldsConfig)
	{
		if (!is_null($this->_groupNode)) {
	    	$this->_groupNode->fields->extend($fieldsConfig->getNode());
		}
	}

	/**
	 * specify the group node to attach to.
	 * @param Varien_Simplexml_Element
	 * */
	public function setAttachmentPoint($groupNode)
	{
		$this->_groupNode = $groupNode;
	}
}