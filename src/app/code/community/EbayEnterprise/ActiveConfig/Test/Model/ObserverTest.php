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

class EbayEnterprise_ActiveConfig_Test_Model_ObserverTest extends EcomDev_PHPUnit_Test_Case
{
	private $observer = null;
	public function setUp()
	{
		$config = Mage::getModel('core/config');
		$config->loadString('
<config>
	<sections>
		<testsection>
			<label>This is a test</label>
			<tab>general</tab>
			<frontend_type>text</frontend_type>
			<sort_order>100</sort_order>
			<show_in_default>1</show_in_default>
			<show_in_website>1</show_in_website>
			<show_in_store>1</show_in_store>
			<groups>
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
				</testgroup>
			</groups>
		</testsection>
	</sections>
</config>'
		);

		$event = $this->getMock('Varien_Event', array('getConfig'));
		$event->expects($this->any())
			->method('getConfig')
			->will($this->returnValue($config));

		$this->observer = $this->getMock('Varien_Object', array('getEvent'));
		$this->observer->expects($this->any())
			->method('getEvent')
			->will($this->returnValue($event));
	}

	public function testProcessConfigImportsGeneratesEvents()
	{
		$model = Mage::getSingleton('activeconfig/observer');
		$model->processConfigImports($this->observer);
		$this->assertEventDispatched('activeconfig_testmodule');
	}
}