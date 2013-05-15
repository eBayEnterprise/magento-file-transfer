<?php
class TrueAction_FileTransfer_Model_Observer
{
	public function handleConfigImport($observer)
	{
		$event = $observer->getEvent();
		$injector = $event->getInjector();
		$helper = mage::helper('filetransfer');
		foreach ($helper->getProtocolCodes() as $protocol) {
			$config = $helper->getProtocolModel(
				$event->getConfigPath(),
				$protocol
			)->getConfig();
			$fields = $config->generateFields($event->getModuleSpec());
			$injector->insertConfig($fields);
		}
	}
}