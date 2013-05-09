<?php
class TrueAction_ActiveConfig_Test_Model_FieldInjectorTests extends EcomDev_PHPUnit_Test_Case
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
		$this->baseConfig = new Varien_Simplexml_Config('
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
		$this->fieldToInsert = new Varien_Simplexml_Config('
		<dummyfield translate="label">
			<label>text field</label>
			<frontend_type>text</frontend_type>
			<sort_order>190</sort_order>
			<show_in_default>1</show_in_default>
			<show_in_website>1</show_in_website>
			<show_in_store>1</show_in_store>
		</dummyfield>
		');
	}

	/**
	 * this test isn't important at all and was only an experiment.
	 * */
	public function testInstantiation()
	{
		$injector = self::$cls->newInstance(
			$this->baseConfig->getNode()
		);
		$this->assertSame(
			$this->baseConfig->getNode(),
			self::$grpNode->getValue($injector)
		);
		$injector = Mage::getModel('activeconfig/fieldinjector');
		$injector->setAttachmentPoint($this->baseConfig->getNode());
		$this->assertSame(
			$this->baseConfig->getNode(),
			self::$grpNode->getValue($injector)
		);
	}

	public function testInsertConfig()
	{
	}
}