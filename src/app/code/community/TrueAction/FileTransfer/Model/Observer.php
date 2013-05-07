<?php
class TrueAction_FileTransfer_Model_Observer
{
	public function handleFtpConfigImport($observer)
	{
		$event = $observer->getEvent();
		$injector = $event->getInjector();
		$generator = Mage::getModel('filetransfer/config_ftp');
		$injector->insertConfig($generator->getConfig(null));
	}
}