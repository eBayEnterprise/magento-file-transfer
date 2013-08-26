<?php
class TrueAction_ActiveConfig_Test_Model_FieldInjectorTest extends EcomDev_PHPUnit_Test_Case
{
	protected static $cls;
	protected static $grpNode;

	public static function setUpBeforeClass()
	{
		self::$cls = new ReflectionClass(
			'TrueAction_ActiveConfig_Model_FieldInjector'
		);
		self::$grpNode = self::$cls->getProperty('_groupNode');
		self::$grpNode->setAccessible(true);
	}

	public function setUp()
	{
		$this->cfg = new Varien_Simplexml_Config('
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
			</fields>
		</testgroup>
		');
		$this->field = new Varien_Simplexml_Config('
		<fields>
			<dummyfield translate="label">
				<label>text field</label>
				<frontend_type>text</frontend_type>
				<sort_order>190</sort_order>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
			</dummyfield>
		</fields>
		');
	}

	public function testInstantiation()
	{
		$injector = self::$cls->newInstance(
			$this->cfg->getNode()
		);
		$this->assertSame(
			$this->cfg->getNode(),
			self::$grpNode->getValue($injector)
		);
	}

	public function testInsertConfig()
	{
		$injector = self::$cls->newInstance();
		$injector->setAttachmentPoint($this->cfg->getNode());
		$injector->insertConfig($this->field);
		$this->assertTrue(isset(
			$this->cfg->getNode()->descend('fields/dummyfield')->sort_order
		));
	}
}
