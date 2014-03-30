<?php
/**
 * EbayEnterprise_ActiveConfig_Model_Config_Abstract
 *
 * @method string getConfigPath()
 * @method self setConfigPath(string $configPath)
 */
abstract class EbayEnterprise_ActiveConfig_Model_Config_Abstract extends Mage_Core_Model_Abstract
{
	/**
	 * @var Varien_Simplexml_Element
	 */
	protected $_importConfig = null;

	/**
	 * Generate the xml nodes that comprise a set of
	 * configuration fields in the magento system config.
	 * @param $importOptions
	 * @return
	 * @todo Complete this docblock.
	 */
	abstract public function generateFields($importOptions);

	/**
	 * Load data to be accessed by magic setter/getters.
	 * @param $configName
	 * @param $magicName
	 * @return
	 * @todo Complete this docblock.
	 */
	protected function _loadFieldAsMagic($configName, $magicName)
	{
		$this->setData(
			$magicName,
			Mage::getStoreConfig(
				$this->getConfigPath() . '/' . $configName,
				$this->getStore()
			)
		);
	}
}
