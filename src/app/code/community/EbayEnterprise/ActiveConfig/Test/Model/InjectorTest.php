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

class EbayEnterprise_ActiveConfig_Test_Model_InjectorTest extends EcomDev_PHPUnit_Test_Case
{
	public static $cls;
	public static $grpNode;

	public static function setUpBeforeClass()
	{
		self::$cls = new ReflectionClass(
			'EbayEnterprise_ActiveConfig_Model_Injector'
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
