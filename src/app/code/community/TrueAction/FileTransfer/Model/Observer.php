<?php
class TrueAction_ActiveConfig_Model_Observer
{
	public function handleFtpConfigImport($observer)
	{
		$event = $observer->getEvent();
		$injector = $event->getInjector();

	}
}