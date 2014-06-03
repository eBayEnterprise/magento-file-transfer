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

class EbayEnterprise_ActiveConfig_Test_Model_Observer_ConfigTest extends EcomDev_PHPUnit_Test_Case_Config
{
	public function testProcessConfigImportsEventConfig()
	{
		$this->assertEventObserverDefined(
			'global',
			'adminhtml_init_system_config',
			'EbayEnterprise_ActiveConfig_Model_Observer',
			'processConfigImports',
			'config_imports_observer'
		);
	}
}
