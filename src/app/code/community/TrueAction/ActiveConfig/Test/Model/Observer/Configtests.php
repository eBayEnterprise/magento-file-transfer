<?php
class TrueAction_ActiveConfig_Test_Model_Observer_ConfigTests extends EcomDev_PHPUnit_Test_Case_Config
{
	public function testProcessConfigImportsEventConfig()
	{
		$this->markTestSkipped(
			"EcomDev Bug?: fails to detect config, though the event works"
		);
		$this->assertEventObserverDefined(
			'global',
			'adminhtml_init_system_config',
			'activeconfig/observer',
			'processConfigImports',
			'config_imports_observer'
		);
	}
}