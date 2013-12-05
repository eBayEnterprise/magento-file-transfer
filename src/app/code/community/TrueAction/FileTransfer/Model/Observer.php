<?php
class TrueAction_FileTransfer_Model_Observer
{
	public function handleConfigImport($observer)
	{
		$event = $observer->getEvent();
		$injector = $event->getInjector();
		$helper = Mage::helper('filetransfer');
		foreach ($helper->getProtocolCodes() as $protocol) {
			$config = $helper->getProtocolModel(
				$event->getConfigPath(),
				$protocol
			)->getConfigModel();
			$fields = $config->generateFields($event->getModuleSpec());
			$injector->insertConfig($fields);
		}
	}
}
