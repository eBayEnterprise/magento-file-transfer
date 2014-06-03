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

class EbayEnterprise_ActiveConfig_Model_Observer
{
	const IMPORT_SPEC_PATH = 'fields/activeconfig_import';

	// format of the event name:
	// activeconfig_<module_config_section>_<featurename>
	const EVENT_PREFIX = 'activeconfig_';

	// the path to the placeholder nodes relative to a group node
	// string
	const IMPORT_SPEC = 'activeconfig_import';

	public function __construct()
	{
		$this->_fieldsCfg = new Varien_Simplexml_Config();
		$this->_fieldsCfg->loadString('<fields/>');
	}

	/**
	 * reads an import specification.
	 * @see README.md
	 * @param Varien_Simplexml_Element $specNode
	 * @param Varien_Simplexml_Element $groupNode
	 * @param string $configPath
	 */
	private function _readImportSpec(
		Varien_Simplexml_Element $specNode,
		Varien_Simplexml_Element $groupNode,
		$configPath
	)
	{
		foreach ($specNode->children() as $moduleName => $moduleNode) {
			Mage::dispatchEvent(
				self::EVENT_PREFIX . $moduleName,
				$this->_prepareEventData(
					$groupNode,
					$moduleNode,
					$configPath
				)
			);
		}
		return $this;
	}

	/**
	 * generates an array to be passed to Mage::dispatchEvent
	 *
	 * @param Varien_Simplexml_Element $moduleSpec
	 * @param Varien_Simplexml_Element $groupNode
	 * @param string $configPath
	 * @return Array(mixed)
	 */
	private function _prepareEventData(
		Varien_Simplexml_Element $groupNode,
		Varien_Simplexml_Element $moduleSpec,
		$configPath
	)
	{
		$injector = Mage::getModel('activeconfig/injector');
		$injector->setAttachmentPoint($groupNode);
		return Array(
			'injector'    => $injector,
			'module_spec' => $moduleSpec,
			'config_path' => $configPath,
		);
	}

	/**
	 * searches for placeholder nodes and replaces them with the specified
	 * configuration nodes.
	 * @param Varien_Simplexml_Element
	 * @param string $configPath
	 */
	private function _processFor(Varien_Simplexml_Element $group, $configPath)
	{
		$fieldNodes = $group->fields->children();
		foreach ($fieldNodes as $fieldName => $fieldNode) {
			if ($fieldName === self::IMPORT_SPEC) {
				$this->_readImportSpec($fieldNode, $group, $configPath);
			}
		}
	}

	/**
	 * this function is run only once after all the system.xml files have been
	 * loaded. it scans each group for the presence of an import specification.
	 * it then uses an injector model to get and insert the proper configuration
	 * as defined in the import specifications.
	 */
	public function processConfigImports($observer)
	{
		$config = $observer->getEvent()->getConfig();
		$sections = $config->getNode('sections');
		foreach ($sections->children() as $sectionName => $section) {
			foreach ($section->groups->children() as $groupName => $group) {
				// only attempt to process groups that have an import spec.
				// NOTE: must specifically check for false or else this may break
				if (false !== $group->descend(self::IMPORT_SPEC_PATH)) {
					$configPath = $sectionName . '/' . $groupName;
					$this->_processFor($group, $configPath);
				}
			}
		}
	}
}
