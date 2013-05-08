<?php
class TrueAction_FileTransfer_Model_Observer
{
	public function handleFtpConfigImport($observer)
	{
		$event = $observer->getEvent();
		$injector = $event->getInjector();
		$fields = Mage::getModel('filetransfer/protocol_config')
			->generateFields($event->getConfig());
		$injector->insertConfig($fields);
	}
}