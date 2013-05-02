<?php

/*
TrueAction_ActiveConfig_Model_Config_Abstract

simple base class to serve as both an example as well as default
behavior.
 */
class TrueAction_ActiveConfig_Model_Config_Abstract
	implements TrueAction_ActiveConfig_Model_Config_Interface
{
	/**
	 * generates the xml nodes that comprise a set of configuration fields
	 * in the magento system config
	 * */
	public function getConfig($importOptions) {
		return Varien_Simplexml_Config()
			->loadString('<fields></fields>');
	}
}
