<?php
class TrueAction_ActiveConfig_Model_Observer
{
	private $_importsXpath = '';

	public function processConfigImports($observer)
	{
		$config = $observer->getEvent()->getConfig();
		$sections = $config->getNode('sections');
		$injector = Mage::getModel('activeconfig/fieldinjector');
		foreach ($sections->children() as $sectionName => $section) {
			foreach ($section->groups->children() as $groupName => $group) {
				// must specifically check for false or else this might break
				if (false !== $group->descend('fields/activeconfig_import')) {
					$injector->processGroup($group);
				}
			}
		}
	}
}