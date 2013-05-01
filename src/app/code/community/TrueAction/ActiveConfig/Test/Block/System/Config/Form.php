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
		$this->_importConfig = $this->modelClass->getProperty(
			'_importConfig'
		);
		$this->_importConfig->setAccessible(true);
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
		$this->assertConfigNodeHasChild('default/sections', 'testsection');
		$cfgNode = Mage::getConfig()->getNode(
			'sections/testsection/groups/testgroup/fields/activeconfig_import',
			'default'
		);
		$this->assertInstanceOf('Mage_Core_Model_Config_Element', $cfgNode);
		$model = $this->modelClass->newInstance();
		$this->_readImportConfig->invoke($model, $cfgNode);
		$this->assertEquals($cfgNode, $this->_importConfig->getValue($model));
	}

	/**
	 * config reading test
	 * @test
	 * @loadFixture importConfig
	 * @noIndexAll
	 * */
	public function testProcessImports()
	{
		$this->assertConfigNodeHasChild('/', 'activeconfig_handler');
		$model = $this->modelClass->newInstance();
		$cfg = null;
		$this->_readImportConfig->invoke($model, $cfg);
	}

	/**
	 * config reading test
	 * @test
	 * @loadFixture importConfig
	 * @noIndexAll
	 * */
	public function testGetConfigGenerator()
	{
		$this->assertConfigNodeHasChild('/', 'activeconfig_handler');
		$model = $this->modelClass->newInstance();
		$this->_getConfigGenerator->invoke($model, 'testsection', 'testfeature');
	}
}