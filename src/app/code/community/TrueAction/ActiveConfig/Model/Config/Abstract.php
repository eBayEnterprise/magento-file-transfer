<?php
/*
TrueAction_ActiveConfig_Model_Config_Abstract

simple base class to serve as both an example as well as default
behavior.
 */
abstract class TrueAction_ActiveConfig_Model_Config_Abstract
	extends Mage_Core_Model_Abstract
	implements TrueAction_ActiveConfig_Model_Config_Interface
{
	// Varien_Simplexml_Element
	protected $_importConfig = null;

	/**
	 * generates the xml nodes that comprise a set of configuration fields
	 * in the magento system config
	 * */
	abstract public function generateFields($importOptions);

	protected function _loadFieldAs($fieldName, $alias)
	{
		$this->setData(
			$alias,
			Mage::getStoreConfig(
				$this->getConfigPath().$fieldName,
				$this->getStore()
			)
		);
	}

	protected function _loadFieldAsMember($fieldName, $memberName)
	{
		$this->$memberName = Mage::getStoreConfig(
			$this->getConfigPath().$fieldName,
			$this->getStore()
		);
	}
}
