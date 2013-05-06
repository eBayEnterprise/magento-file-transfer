<?php
/**
 * handles inserting the external system config xml nodes.
 * */
class TrueAction_ActiveConfig_Model_FieldInjector extends Mage_Core_Model_Abstract
{

	const HANDLER_TAG = 'activeconfig_handler';

	// the path to the placeholder nodes relative to a group node
	// string
	private $_importNodeName = 'activeconfig_import';

	// the import setting nodes to be replaced by the new config.
	// Varien_Simplexml_Element
	private $_importNode = null;

	// the fields node that will become the parent of the newly generated
	// config nodes.
	// Varien_Simplexml_Element
	private $_fieldsCfg = null;

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
				$generator = $this->_getConfigGenerator($moduleName, $feature);
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
	 * searches for placeholder nodes and replaces them with the specified
	 * configuration nodes.
	 * @param Varien_Simplexml_Element
	 * */
	public function processGroup($group)
	{
		Mage::log("group before:\n" . print_r($group, true));
		$fieldNodes = $group->fields->children();
		foreach ($fieldNodes as $fieldName => $fieldNode) {
        	if ($fieldName === $this->_importNodeName) {
        		$this->_readImportConfig($fieldNode);
	        	$group->fields->extend($this->_fieldsCfg->getNode());
        	}
        }
		Mage::log("group after:\n" . print_r($group, true));
		Mage::log("group after:\n" . print_r($group->getParent(), true));
	}
}