<?php
/**
 * Copyright (c) 2013-2014 eBay Enterprise, Inc.
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @copyright   Copyright (c) 2013-2014 eBay Enterprise, Inc. (http://www.ebayenterprise.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class EbayEnterprise_FileTransfer_Test_Model_ObserverTest
	extends EbayEnterprise_FileTransfer_Test_Abstract
{
	/**
	 * Get a mock of the abstract protocol model which will have all abstract
	 * methods mocked.
	 * @return Mock_FileTransfer_Model_Protocol_Abstract
	 */
	protected function _getProtocolMock()
	{
		$mock = $this->getModelMockBuilder('filetransfer/protocol_abstract')
			->disableOriginalConstructor()
			->setMethods(array('sendFile', 'sendAllFiles', 'getAllFiles', 'getFile', 'deleteFile'))
			->getMock();
		return $mock;
	}
	/**
	 * Config import should iterate over any available protocol codes and inject
	 * fields into config via the injector passed in with the event observer.
	 * @mock Varien_Event_Observer::getEvent
	 * @mock Varien_Event::getInjector
	 * @mock Varien_Event::getConfigPath
	 * @mock Varien_Event::getModuleSpec
	 * @mock EbayEnterprise_ActiveConfig_Model_Injector::insertConfig ensure all the proper config is injected
	 * @mock EbayEnterprise_FileTransfer_Helper_Data::getProtocolCodes mock out list of available protocol codes
	 * @mock EbayEnterprise_FileTransfer_Helper_Data::getProtocolModels return the mock sftp protocol model
	 * @mock EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::getConfigModel return a mocked config model
	 * @mock EbayEnterprise_FileTrnasfer_Model_Protocol_Types_Sftp_Config::generateFields return a know Varien_Simplexml_Element
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
	/**
	 * Running all file transfers should collect the remote host configuration,
	 * run all imports and then run all exports.
	 * @test
	 */
	public function testRunTransfers()
	{
		$observer = $this->getModelMock(
			'filetransfer/observer',
			array('_getRegisteredConfigurations', '_importFiles', '_exportFiles'));

		$remoteConfiguration = array(
			'some_filetransfer_config_registration' => 'config/path/to/ft/config',
		);

		$observer->expects($this->once())
			->method('_getRegisteredConfigurations')
			->will($this->returnValue($remoteConfiguration));
		$observer->expects($this->once())
			->method('_importFiles')
			->with($this->identicalTo($remoteConfiguration))
			->will($this->returnSelf());
		$observer->expects($this->once())
			->method('_exportFiles')
			->with($this->identicalTo($remoteConfiguration))
			->will($this->returnSelf());
		$this->assertSame($observer, $observer->runTransfers());
	}
	/**
	 * Use the filetransfer/registry configuration to get a list of all other
	 * configuration setup within other modules for FileTransfer
	 * @test
	 */
	public function testGetRegisteredConfigurations()
	{
		$registryConfig = array('some_module' => 'some_module/config');

		$observer = $this->getModelMock('filetransfer/observer', array('_lookupConfig'));
		$observer->expects($this->any())
			->method('_lookupConfig')
			->with($this->identicalTo('filetransfer/registry'))
			->will($this->returnValue($registryConfig));
		$method = new ReflectionMethod($observer, '_getRegisteredConfigurations');
		$method->setAccessible(true);
		$this->assertSame(
			$registryConfig,
			$method->invoke($observer)
		);
	}
	/**
	 * When no additional FileTransfer config has been set up, config will return
	 * and empty string, this method should still return an array
	 * @test
	 */
	public function testGetRemoteConfigurationsEmpty()
	{
		$registryConfig = array();

		$observer = $this->getModelMock('filetransfer/observer', array('_lookupConfig'));
		$observer->expects($this->any())
			->method('_lookupConfig')
			->will($this->returnValueMap(array(
				array('filetransfer/registry', $registryConfig),
			)));
		$method = new ReflectionMethod($observer, '_getRegisteredConfigurations');
		$method->setAccessible(true);
		$this->assertSame(
			array(),
			array_values($method->invoke($observer))
		);
	}
	/**
	 * Provides two arrays for test methods that need to use remote host config.
	 * The first is an array of config paths. The second is an array of remote
	 * configurations. The arrays are parallel so the paths in the first array
	 * are expected to resolve to the matching config in the second array.
	 * @return array
	 */
	public function provideRemoteHostConfig()
	{
		return array(
			array(
				array('path/to/full/config', 'path/to/empty/config', 'path/to/missing/config'),
				array(
					array(
						'filetransfer_protocol' => 'sftp',
						'filetransfer_imports' => array(
							'valid' => array(
								'local_directory' => 'local/outbox',
								'remote_directory' => 'remote/inbox',
								'file_pattern' => 'glob*'
							),
							'invalid' => array('nothing' => 'nope'),
						),
						'filetransfer_exports' => array(
							'valid' => array(
								'local_directory' => 'local/outbox',
								'remote_directory' => 'remote/inbox',
								'file_pattern' => 'glob*',
								'sent_directory' => 'local/sent',
							),
							'invalid' => array('nothing' => 'nope'),
						),
					),
				),
				array(
					array(
						'filetransfer_protocol' => 'sftp',
						'filetransfer_imports' => array(),
						'filetransfer_exports' => array(),
					),
				),
				array(
					array(
						'filetransfer_protocol' => 'sftp',
					),
				)
			)
		);
	}
	/**
	 * Import files should go through all remote configurations and for each one,
	 * create a new protocol model, and get all files from each configured remote
	 * directory, and retrieve them to the configured local directory.
	 * @param array $configPaths array of registered config paths
	 * @param array $configData array of FT configurations
	 * @test
	 * @dataProvider provideRemoteHostConfig
	 */
	public function testImportFiles($configPaths, $configData)
	{
		$configValueMap = array_map(
			function ($p, $d) { return array($p, $d); },
			$configPaths, $configData
		);
		$observer = $this->getModelMock(
			'filetransfer/observer',
			array('_dispatchEvent', '_importFromRemote', '_lookupConfig')
		);
		// ensure files are imported before the event is dispatched
		$observer->expects($this->any())
			->method('_lookupConfig')
			->will($this->returnValueMap($configValueMap));
		$observer->expects($this->once())
			->method('_importFromRemote')
			->with($this->identicalTo($configPaths[0]), $this->identicalTo($configData[0]))
			->will($this->returnSelf());
		$observer->expects($this->once())
			->method('_dispatchEvent')
			->with($this->identicalTo('filetransfer_import_complete'))
			->will($this->returnSelf());
		$method = new ReflectionMethod($observer, '_importFiles');
		$method->setAccessible(true);
		$this->assertSame(
			$observer,
			$method->invoke($observer, $configPaths)
		);
	}
	/**
	 * Import all files from a single remote host. For each configured
	 * local/remote pair, get all files from the remote and delete any files
	 * retrieved from the remote.
	 * The provideRemoteHostConfig provider is used for this test to all the same
	 * config values to be used for all the import and export tests, however, this
	 * method will only ever be called with one remote host config at a time.
	 * The method should also only ever be called with a remote host config
	 * containing at least one pair of local/remote directories. In the case of
	 * the data from teh provider, this is only ever the first set of data.
	 * @param array $configPaths config path to filetransfer remote host config - only the first is used in this test
	 * @param array $configData sets of remote host configuration - only the first is used in this test
	 * @test
	 * @dataProvider provideRemoteHostConfig
	 */
	public function testImportFromRemote($configPaths, $configData)
	{
		$retrievedFiles = array(
			array(
				'remote' => '/path/to/remote/file.xml',
				'local' => '/path/to/local/file.xml',
			),
			array(
				'remote' => '/path/to/remote/other_file.xml',
				'local' => '/path/to/local/other_file.xml'
			)
		);

		$configPath = $configPaths[0];
		$hostConfig = $configData[0];

		$observer = $this->getModelMock('filetransfer/observer', array('_isImportDirectoryPairValid'));
		$helper = $this->getHelperMock('filetransfer/data', array('getProtocolModel'));
		$protocol = $this->_getProtocolMock();

		$this->replaceByMock('helper', 'filetransfer', $helper);

		$observer->expects($this->exactly(2))
			->method('_isImportDirectoryPairValid')
			->will($this->returnValueMap(array(
				array($hostConfig['filetransfer_imports']['valid'], true),
				array($hostConfig['filetransfer_imports']['invalid'], false),
			)));
		$helper->expects($this->once())
			->method('getProtocolModel')
			->with($this->identicalTo($configPath))
			->will($this->returnValue($protocol));
		$protocol->expects($this->once())
			->method('getAllFiles')
			->with(
				$this->identicalTo(Mage::getBaseDir('var') . DS . $hostConfig['filetransfer_imports']['valid']['local_directory']),
				$this->identicalTo($hostConfig['filetransfer_imports']['valid']['remote_directory']),
				$this->identicalTo($hostConfig['filetransfer_imports']['valid']['file_pattern'])
			)
			->will($this->returnValue($retrievedFiles));
		$protocol->expects($this->exactly(2))
			->method('deleteFile');
		$protocol->expects($this->at(1))
			->method('deleteFile')
			->with($this->identicalTo($retrievedFiles[0]['remote']))
			->will($this->returnValue(true));
		$protocol->expects($this->at(2))
			->method('deleteFile')
			->with($this->identicalTo($retrievedFiles[1]['remote']))
			->will($this->returnValue(true));

		$method = new ReflectionMethod($observer, '_importFromRemote');
		$method->setAccessible(true);
		// use only the first config path which is expected to have fully configured
		// imports and exports
		$this->assertSame($observer, $method->invoke($observer, $configPath, $hostConfig));
	}
	/**
	 * Export files should go through all remote configurations and for each one,
	 * create a new protocol model, and get all files from each configured local
	 * directory and put them on the remote.
	 * @param array $configPaths config path to filetransfer remote host config
	 * @param array $configData sets of remote host configuration
	 * @test
	 * @dataProvider provideRemoteHostConfig
	 */
	public function testExportFiles($configPaths, $configData)
	{
		$configValueMap = array_map(
			function ($p, $d) { return array($p, $d); },
			$configPaths, $configData
		);
		$observer = $this->getModelMock(
			'filetransfer/observer',
			array('_dispatchEvent', '_exportToRemote', '_lookupConfig')
		);
		// ensure the files are exported before the event is dispatched
		$observer->expects($this->any())
			->method('_lookupConfig')
			->will($this->returnValueMap($configValueMap));
		$observer->expects($this->once())
			->method('_exportToRemote')
			->with($this->identicalTo($configPaths[0]), $this->identicalTo($configData[0]))
			->will($this->returnSelf());
		$observer->expects($this->once())
			->method('_dispatchEvent')
			->with($this->identicalTo('filetransfer_export_complete'))
			->will($this->returnSelf());
		$method = new ReflectionMethod($observer, '_exportFiles');
		$method->setAccessible(true);
		$this->assertSame($observer, $method->invoke($observer, $configPaths));
	}
	/**
	 * Export all local files to a remote directory - for a single remote host,
	 * go through each configured local/remote pair and send all files in the
	 * local directory to the remote. Each files successfully sent to the remote
	 * should be moved from its current location to a configured "sent" directory.
	 * The provideRemoteHostConfig provider is used for this test to all the same
	 * config values to be used for all the import and export tests, however, this
	 * method will only ever be called with one remote host config at a time.
	 * The method should also only ever be called with a remote host config
	 * containing at least one pair of local/remote directories. In the case of
	 * the data from teh provider, this is only ever the first set of data.
	 * @param array $configPaths Config path to filetransfer remote host config - only the first actually matters to this test
	 * @param array $configData All configured remote hosts - only the first actually matters to this test
	 * @test
	 * @dataProvider provideRemoteHostConfig
	 */
	public function testExportToRemote($configPaths, $configData)
	{
		$sentFiles = array(
			array(
				'local' => '/path/to/local/file.xml',
				'remote' => '/path/to/remote/file.xml'
			),
			array(
				'local' => '/path/to/local/other_file.xml',
				'remote' => '/path/to/remote/other_file.xml',
			),
		);
		// First set of config data expected to have fully configured imports
		// and exports and should be the only config data used within this method.
		$hostConfig = $configData[0];
		$configPath = $configPaths[0];

		$observer = $this->getModelMock('filetransfer/observer', array('_isExportDirectoryPairValid'));
		$helper = $this->getHelperMock('filetransfer/data', array('getProtocolModel'));
		$fileHelper = $this->getHelperMock('filetransfer/file', array('mvToDir'));
		$protocol = $this->_getProtocolMock();

		$this->replaceByMock('helper', 'filetransfer', $helper);
		$this->replaceByMock('helper', 'filetransfer/file', $fileHelper);

		$observer->expects($this->exactly(2))
			->method('_isExportDirectoryPairValid')
			->will($this->returnValueMap(array(
				array($hostConfig['filetransfer_exports']['valid'], true),
				array($hostConfig['filetransfer_exports']['invalid'], false),
			)));
		$helper->expects($this->once())
			->method('getProtocolModel')
			->with($this->identicalTo($configPath))
			->will($this->returnValue($protocol));
		$protocol->expects($this->once())
			->method('sendAllFiles')
			->with(
				$this->identicalTo(Mage::getBaseDir('var') . DS . $hostConfig['filetransfer_exports']['valid']['local_directory']),
				$this->identicalTo($hostConfig['filetransfer_exports']['valid']['remote_directory']),
				$this->identicalTo($hostConfig['filetransfer_exports']['valid']['file_pattern'])
			)
			->will($this->returnValue($sentFiles));
		$fileHelper->expects($this->exactly(2))
			->method('mvToDir');
		$fileHelper->expects($this->at(0))
			->method('mvToDir')
			->with(
				$this->identicalTo($sentFiles[0]['local']),
				$this->identicalTo($hostConfig['filetransfer_exports']['valid']['sent_directory'] . DS . basename($sentFiles[0]['local']))
			)
			->will($this->returnValue(true));
		$fileHelper->expects($this->at(1))
			->method('mvToDir')
			->with(
				$this->identicalTo($sentFiles[1]['local']),
				$this->identicalTo($hostConfig['filetransfer_exports']['valid']['sent_directory'] . DS . basename($sentFiles[1]['local']))
			)
			->will($this->returnValue(true));

		$method = new ReflectionMethod($observer, '_exportToRemote');
		$method->setAccessible(true);
		// use only the first config path which is expected to have fully configured
		// imports and exports
		$this->assertSame($observer, $method->invoke($observer, $configPath, $hostConfig));
	}
	/**
	 * Provide sets of local/remote directory pairs, if the pair requires export
	 * pair validation (requires sent_directory) and whether or not the given
	 * pairs should be considered valid.
	 * @return array
	 */
	public function provideDirectoryPairs()
	{
		return array(
			array(
				array('remote_directory' => 'some/remote', 'local_directory' => 'local', 'sent_directory' => 'sent', 'file_pattern' => 'glob*'),
				true,
				true
			),
			array(
				array('remote_directory' => 'some/remote', 'local_directory' => 'local', 'file_pattern' => 'glob*'),
				false,
				true
			),
			array(
				array('remote_directory' => 'remote'),
				false,
				false
			),
			array(
				array('local_directory' => 'local'),
				false,
				false
			),
		);
	}
	/**
	 * Test for the dirPair to be valid, containing all fields necessary for
	 * the import/export process. If the pairing is valid, should return true,
	 * otherwise false.
	 * @param array   $dirPair   array of configured directories
	 * @param boolean $isExport  is export pair validation required
	 * @param boolean $isValid   are all configured pairs valid
	 * @test
	 * @dataProvider provideDirectoryPairs
	 */
	public function testIsDirectoryPairValid($dirPair, $isExport, $isValid)
	{
		$observer = Mage::getModel('filetransfer/observer');
		if ($isExport) {
			$method = new ReflectionMethod($observer, '_isExportDirectoryPairValid');
		} else {
			$method = new ReflectionMethod($observer, '_isImportDirectoryPairValid');
		}
		$method->setAccessible(true);
		$this->assertSame($isValid, $method->invoke($observer, $dirPair, $isExport));
	}
}
