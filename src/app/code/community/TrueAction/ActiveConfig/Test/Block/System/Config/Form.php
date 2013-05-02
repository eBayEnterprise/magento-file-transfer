<?php
class TrueAction_ActiveConfig_Test_Block_System_Config_Form extends EcomDev_PHPUnit_Test_Case_Config
{
	public function setUp()
	{
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
	public function testReadImportConfig()
	{
		$cfgNode = Mage::getConfig()->getNode(
			'testsection/testgroup/activeconfig_import',
			'default'
		);
		$this->assertInstanceOf('Mage_Core_Model_Config_Element', $cfgNode);
		$this->assertConfigNodeHasChild(
			'testsection/testgroup/activeconfig_import',
			'testmodule'
		);
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
	public function testGetConfigGenerator()
	{
		$this->markTestIncomplete();
		$cfgNode = Mage::getConfig()->getNode(
			'activeconfig_handler/testmodule/testfeature'
		);
		$this->assertInstanceOf('Mage_Core_Model_Config_Element', $cfgNode);
		$model          = $this->modelClass->newInstance();
		$generatorModel = $this->_getConfigGenerator->invoke(
			$model,
			'testmodule',
			'testfeature'
		);
		$this->assertInstanceOf(
			'TrueAction_ActiveConfig_Model_Generator',
			$generatorModel
		);
	}

	/**
	 * config reading test
	 * @test
	 * @loadFixture importConfig
	 * @noIndexAll
	 * */
	public function testProcessImports()
	{
		$this->markTestIncomplete();
		$this->assertConfigNodeHasChild('activeconfig_handler', 'testmodule');
		$model = $this->modelClass->newInstance();
	}
}