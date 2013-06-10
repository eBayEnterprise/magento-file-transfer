<?php
/*
TrueAction_ActiveConfig_Model_Config_Abstract

simple base class to serve as both an example as well as default
behavior.
 */
abstract class TrueAction_ActiveConfig_Model_Config_Abstract
	extends Mage_Core_Model_Abstract
{
	// Varien_Simplexml_Element
	protected $_importConfig = null;

	/**
	 * generates the xml nodes that comprise a set of configuration fields
	 * in the magento system config
	 * */
	abstract public function generateFields($importOptions);

	// public function setConfigPath
	// public function getConfigPath

	/**
	 * loads data to be accessed by magic setter/getters
	 * */
	protected function _loadFieldAsMagic($configName, $magicName)
	{
		$this->setData(
			$magicName,
			Mage::getStoreConfig(
				$this->getConfigPath().'/'.$configName,
				$this->getStore()
			)
		);
	}

	/**
	 * loads data to be accessed as members of this class
	 * */
	protected function _loadFieldAsMember($configName, $memberName)
	{
		$this->$memberName = Mage::getStoreConfig(
			$this->getConfigPath().$configName,
			$this->getStore()
		);
	}
}
