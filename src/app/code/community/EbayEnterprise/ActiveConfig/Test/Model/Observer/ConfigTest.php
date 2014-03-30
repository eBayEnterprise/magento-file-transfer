<?php
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
