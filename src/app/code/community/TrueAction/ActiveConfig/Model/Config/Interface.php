<?php

/*
TrueAction_ActiveConfig_Model_Generator

Interface class for all configuration generators
 */
interface TrueAction_ActiveConfig_Model_Config_Interface {

	/**
	 * generates the xml nodes that comprise a set of configuration fields
	 * in the magento system config
	 * */
	public function generateFields($importOptions);
}
