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
