<?php
/**
 * handles inserting the external system config xml nodes.
 * */
class TrueAction_ActiveConfig_Model_FieldInjector
{
	// the fields node that will become the parent of the newly generated
	// config nodes.
	// Varien_Simplexml_Element
	private $_fieldsConfig = null;

	public function __construct()
	{
		parent::__construct();
		$this->_fieldsCfg = new Varien_Simplexml_Config();
		$this->_fieldsCfg->loadString('<fields/>');
	}

	/**
	 * inserts the field configuration nodes into the attached fieldset
	 * @param Varien_Simplexml_Config $fieldsConfig
	 * */
	public function insertConfig($fieldsConfig)
	{
    	$this->_fieldsConfig->extend($fieldsConfig->getNode());
	}
}