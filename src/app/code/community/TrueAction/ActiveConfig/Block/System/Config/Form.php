<?php
/**
 * handles inserting the external system config xml nodes.
 * */
class TrueAction_ActiveConfig_Block_System_Config_Form extends Mage_Adminhtml_Block_System_Config_Form {

	const HANDLER_TAG = 'activeconfig_handler';

	// the path to the placeholder nodes relative to a group node
	// string
	private $_importNodePath = 'fields';

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
	 * removes a node and returns the node that was its immediate parent.
	 * @return Varien_Simplexml_Element
	 * */
	private function _removeNode($element)
	{
		$parentNode = null;
		return $parentNode;
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
			$model = Mage::getModel($generatorNode->innerXml());
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
	private function _processImports($group)
	{
        $lookForImports = true;
        while ($lookForImports) {
        	$lookForImports = false;
        	$importNode = $group->descend($this->_importNodePath);
        	if ($importNode != $group) {
        		$this->_readImportConfig($importNode);
            	$lookForImports = true;
        	}
        }
		return $this;
	}


    /**
     * gathers the config and sets up a form object used when redering the form.
     * It searches each section for any import nodes and then replaces the node
     * with the specified module-specific config nodes.
     *
     * @return Mage_Adminhtml_Block_System_Config_Form
     */
    public function initForm()
    {
        $this->_initObjects();

        $form = new Varien_Data_Form();

        $sections = $this->_configFields->getSection(
            $this->getSectionCode(),
            $this->getWebsiteCode(),
            $this->getStoreCode()
        );
        if (empty($sections)) {
            $sections = array();
        }
        foreach ($sections as $section) {
            /* @var $section Varien_Simplexml_Element */
            if (!$this->_canShowField($section)) {
                continue;
            }
            foreach ($section->groups as $groups){
                $groups = (array)$groups;
                usort($groups, array($this, '_sortForm'));

                foreach ($groups as $group){
                    /* @var $group Varien_Simplexml_Element */
                    if (!$this->_canShowField($group)) {
                        continue;
                    }
                    $this->_processImports($group);
                    $this->_initGroup($form, $group, $section);
                }
            }
        }

        $this->setForm($form);
        return $this;
    }
}