<?php

class TrueAction_FileTransfer_Test_Model_ObserverTest
	extends TrueAction_FileTransfer_Test_Abstract
{

	/**
	 * Config import should iterate over any available protocol codes and inject
	 * fields into config via the injector passed in with the event observer.
	 * @mock Varien_Event_Observer::getEvent
	 * @mock Varien_Event::getInjector
	 * @mock Varien_Event::getConfigPath
	 * @mock Varien_Event::getModuleSpec
	 * @mock TrueAction_ActiveConfig_Model_Injector::insertConfig ensure all the proper config is injected
	 * @mock TrueAction_FileTransfer_Helper_Data::getProtocolCodes mock out list of available protocol codes
	 * @mock TrueAction_FileTransfer_Helper_Data::getProtocolModels return the mock sftp protocol model
	 * @mock TrueAction_FileTransfer_Model_Protocol_Types_Sftp::getConfigModel return a mocked config model
	 * @mock TrueAction_FileTrnasfer_Model_Protocol_Types_Sftp_Config::generateFields return a know Varien_Simplexml_Element
	 *
	 * @return [type] [description]
	 */
	public function testHandleConfigImport()
	{
		$event = $this
			->getMock('Varien_Event', array('getInjector', 'getConfigPath', 'getModuleSpec'));
		$observer = $this
			->getMock('Varien_Event_Observer', array('getEvent'));
		$injector = $this
			->getModelMockBuilder('activeconfig/injector')
			->disableOriginalConstructor()
			->setMethods(array('insertConfig'))
			->getMock();
		$helper = $this
			->getHelperMock('filetransfer/data', array('getProtocolCodes', 'getProtocolModel'));
		$this->replaceByMock('helper', 'filetransfer', $helper);
		// the known module spec xml
		$moduleSpec = new Varien_Simplexml_Element('<module_spec></module_spec>');
		// the known config fields to inject
		$fields = new Varien_Simplexml_Element('<config_fields></config_fields>');
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->disableOriginalConstructor()
			->setMethods(array('getConfigModel'))
			->getMock();
		$config = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp_config')
			->disableOriginalConstructor()
			->setMethods(array('generateFields'))
			->getMock();

		$observer
			->expects($this->any())
			->method('getEvent')
			->will($this->returnValue($event));
		$event
			->expects($this->any())
			->method('getInjector')
			->will($this->returnValue($injector));
		$event
			->expects($this->any())
			->method('getConfigPath')
			->will($this->returnValue('config/path'));
		$event
			->expects($this->any())
			->method('getModuleSpec')
			->will($this->returnValue($moduleSpec));
		$helper
			->expects($this->any())
			->method('getProtocolCodes')
			->will($this->returnValue(array('sftp')));
		$helper
			->expects($this->once())
			->method('getProtocolModel')
			->with($this->identicalTo('config/path'), $this->identicalTo('sftp'), $this->isNull())
			->will($this->returnValue($sftp));
		$sftp
			->expects($this->any())
			->method('getConfigModel')
			->will($this->returnValue($config));
		$config
			->expects($this->any())
			->method('generateFields')
			->with($this->identicalTo($moduleSpec))
			->will($this->returnValue($fields));
		$injector
			->expects($this->once())
			->method('insertConfig')
			->with($this->identicalTo($fields));
		Mage::getModel('filetransfer/observer')->handleConfigImport($observer);
	}
}
