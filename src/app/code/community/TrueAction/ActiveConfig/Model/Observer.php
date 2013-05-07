<?php
class TrueAction_ActiveConfig_Model_Observer
{
	private $_importsXpath = '';
	private $_importSpecNode = 'fields/activeconfig_import';


	const HANDLER_TAG = 'activeconfig_handler';

	// format of the event name:
	// activeconfig_<module_config_section>_<featurename>
	const EVENT_FORMAT = 'activeconfig_%s_%s';

	// the path to the placeholder nodes relative to a group node
	// string
	const IMPORT_NODE = 'activeconfig_import';

	// the import setting nodes to be replaced by the new config.
	// Varien_Simplexml_Element
	private $_importNode = null;

	// the fields node that will become the parent of the newly generated
	// config nodes.
	// Varien_Simplexml_Element
	private $_fieldsConfig = null;

	// the event to fire to insert the configuration
	private $_eventName = '';

	public function __construct()
	{
		parent::__construct();
		$this->_fieldsCfg = new Varien_Simplexml_Config();
		$this->_fieldsCfg->loadString('<fields/>');
	}

	/**
	 * expectes config to be of the following structure
	 *
	 * <config><sections>...<groups>...<fields>
     *  <activeconfig_import> <!--signals that an import is necessary -->
     *    <module>            <!--module whose feature config we want to add-->
     *      <feature/>        <!--feature whose config we're importing -->
     *      <feature2>
     *         ...            <!--config related to actual import-->
     *      </feature2>
     *    </module>
     *    ...
     *  </activeconfig_import>
	 *
	 * @param Varien_Simplexml_Element $importNode
	 * */
	private function _readImportConfig($importNode)
	{
		$this->_importNode = $importNode;
		foreach ($importNode->children() as $moduleName => $moduleNode) {
			foreach ($moduleNode->children() as $feature => $featureNode) {
				$generator = $this->_generateEventName($moduleName, $feature);
				$config    = $generator->getConfig($featureNode);
				$this->_fieldsCfg->extend($config);
			}
		}
		return $this;
	}

	/**
	 * returns a concrete injection class who is responsible for inserting
	 * new nodes into the system config structure.
	 *
	 * reads the followig config structure:
	 *
	 * <config>
	 *  <activeconfig_handler>
	 *   <module>
	 *    <feature>module/module:method</feature>
	 *    ...
	 *   </module>
	 *   </module>
	 *  </activeconfig_handler>
	 * </config>
	 *
	 * @param string $module
	 * @return TrueAction_ActiveConfig_Model_Config_Abstract
	 * */
	private function _getConfigGenerator($module, $feature)
	{
		$model = null;
		$generatorNode = Mage::getConfig()->getNode(
			self::HANDLER_TAG . '/' . $module  . '/' .  $feature
		);
		if ($generatorNode) {
			$model = new TrueAction_FileTransfer_Model_Config_Ftp();
			// $model = Mage::getModel($generatorNode->innerXml());
		}
		if (!$model) {
			$model = new TrueAction_ActiveConfig_Model_Config_Abstract();
		}
		return $model;
	}

	/**
	 * this function is run only once after all the system.xml files have been
	 * loaded. it scans each group for the presence of an import specification.
	 * it then uses an injector model to get and insert the proper configuration
	 * as defined in the import specifications.
	 * */
	public function processConfigImports($observer)
	{
		$config = $observer->getEvent()->getConfig();
		$sections = $config->getNode('sections');
		$injector = Mage::getModel('activeconfig/fieldinjector');
		foreach ($sections->children() as $sectionName => $section) {
			foreach ($section->groups->children() as $groupName => $group) {
				// must specifically check for false or else this might break
				if (false !== $group->descend('fields/activeconfig_import')) {
					$injector->processGroup($group);
				}
			}
		}
	}
}