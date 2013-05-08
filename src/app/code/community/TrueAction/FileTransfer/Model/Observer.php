<?php
class TrueAction_FileTransfer_Model_Observer
{
	public function handleConfigImport($observer)
	{
		$event = $observer->getEvent();
		$injector = $event->getInjector();
		$config = Mage::helper('filetransfer')
			->getProtocolModel($event->getConfigPath())
			->getConfig();
		$fields = $config->generateFields($event->getModuleSpec());
		Mage::log(print_r($fields, true));
		$injector->insertConfig($fields);
	}
}