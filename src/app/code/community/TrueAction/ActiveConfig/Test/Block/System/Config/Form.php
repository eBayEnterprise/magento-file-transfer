<?php
class TrueAction_ActiveConfig_Test_Block_System_Config_Form extends EcomDev_PHPUnit_Test_Case_Config
{
	public function setUp()
	{
		parent::setUp();
		$this->setCurrentStore('default');
		$this->modelClass = new ReflectionClass(
			'TrueAction_ActiveConfig_Block_System_Config_Form'
		);
		$this->_readImportConfig = $this->modelClass->getMethod(
			'_readImportConfig'
		);
		$this->_readImportConfig->setAccessible(true);
		$this->_importNode = $this->modelClass->getProperty(
			'_importNode'
		);
		$this->_importNode->setAccessible(true);
		$this->_getConfigGenerator = $this->modelClass->getMethod(
			'_getConfigGenerator'
		);
		$this->_getConfigGenerator->setAccessible(true);
	}

	/**
	 * config reading test
	 * @test
	 * @loadFixture importConfig
	 * @noIndexAll
	 * */
	public function testGetConfigGenerator()
	{
		$path = $this->modelClass->getConstant('HANDLER_TAG') . '/testmodule/testfeature';
		$this->assertEquals('activeconfig_handler/testmodule/testfeature', $path);
		$cfgNode = Mage::getConfig()->getNode($path);
		$this->assertInstanceOf('Mage_Core_Model_Config_Element', $cfgNode);
		$model          = $this->modelClass->newInstance();
		$generatorModel = $this->_getConfigGenerator->invoke(
			$model,
			'testmodule',
			'testfeature'
		);
		$this->assertInstanceOf(
			'TrueAction_ActiveConfig_Model_Config_Interface',
			$generatorModel
		);
	}

	/**
	 * config reading test
	 * @test
	 * @loadFixture importConfig
	 * @noIndexAll
	 * */
	public function testReadImportConfig()
	{
		$cfg = new Varien_Simplexml_Config();
		$cfg->loadString('
			<activeconfig_import>
			 <filetransfer>
			  <ftp/>
			 </filetransfer>
		    </activeconfig_import>
		');
		$cfgNode = $cfg->getNode();
		$this->assertInstanceOf('Varien_Simplexml_Element', $cfgNode);
		$model = $this->modelClass->newInstance();
		$this->_readImportConfig->invoke($model, $cfgNode);
		$this->assertEquals($cfgNode, $this->_importNode->getValue($model));
	}

	/**
	 * config reading test
	 * @test
	 * @loadFixture importConfig
	 * @noIndexAll
	 * */
	public function testProcessImports()
	{
		$xml = '
		<testgroup translate="label">
			<label>TestGroup</label>
			<frontend_type>text</frontend_type>
			<sort_order>1</sort_order>
			<show_in_default>1</show_in_default>
			<show_in_website>1</show_in_website>
			<show_in_store>1</show_in_store>
			<fields>
				<activeconfig_import>
					<testmodule>
						<testfeature>
							<label>Remote Path</label>
							<frontend_type>text</frontend_type>
							<sort_order>190</sort_order>
							<show_in_default>1</show_in_default>
							<show_in_website>1</show_in_website>
							<show_in_store>1</show_in_store>
						</testfeature>
					</testmodule>
				</activeconfig_import>
				<dummyfield translate="label">
					<label>text field</label>
					<frontend_type>text</frontend_type>
					<sort_order>190</sort_order>
					<show_in_default>1</show_in_default>
					<show_in_website>1</show_in_website>
					<show_in_store>1</show_in_store>
				</dummyfield>
			</fields>
		</testgroup>';

		$processImports = $this->modelClass->getMethod('_processImports');
		$processImports->setAccessible(true);
		$this->assertConfigNodeHasChild('activeconfig_handler', 'testmodule');
		$model = $this->modelClass->newInstance();
		$groupCfg = Mage::getModel('adminhtml/config');
		$groupCfg->loadString($xml);
		print $groupCfg->getNode();
		$this->assertInstanceOf('Varien_Simplexml_Element', $groupCfg->getNode());
		$processImports->invoke($model, $groupCfg->getNode());

		$fieldsCfg = $this->modelClass->getProperty('_fieldsCfg');
		$fieldsCfg->setAccessible(true);
		$this->assertTrue($fieldsCfg->getValue($model)->getNode());
	}
}