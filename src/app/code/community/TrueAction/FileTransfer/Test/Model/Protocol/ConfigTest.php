<?php
class TrueAction_FileTransfer_Test_Model_Protocol_ConfigTest extends EcomDev_PHPUnit_Test_Case
{
	private static $_config = array(
		'protocol_code' => 'ftp',
		'config_path' => 'testsection/testgroup'
	);

	private static $_fieldMap = array(
		'filetransfer_ftp_username'    => 'username',
		'filetransfer_ftp_password'    => 'password',
		'filetransfer_ftp_host'        => 'host',
		'filetransfer_ftp_port'        => 'port',
		'filetransfer_ftp_remote_path' => 'remote_path',
	);

	public function setUp()
	{
		$this->class = new ReflectionClass(
			'TrueAction_FileTransfer_Model_Protocol_Config'
		);
		$this->importOptions = new Varien_Simplexml_Config(
			'<filetransfer>
				<sort_order>190</sort_order>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
				<ftp></ftp>
				<sftp></sftp>
			</filetransfer>'
		);
	}

	/**
	 * verify the configuration is generated.
	 * @test
	 */
	public function testGenerateConfig()
	{
		$model = $this->getModelMockBuilder('filetransfer/protocol_config')
			->disableOriginalConstructor()
			->setMethods(array('none'))
			->getMock();
		$config = $model->setConfigPath('testsection/testgroup')
			->setProtocolCode('ftp')
			->generateFields($this->importOptions);
		$this->assertInstanceOf('Varien_Simplexml_Config', $config);
		$node = $config->getNode();
		$this->assertInstanceOf('Varien_Simplexml_Element', $node);
		$this->assertTrue($node->hasChildren());
		$names = array();
		foreach ($node as $name => $node) {
			$this->assertInstanceOf('Varien_Simplexml_Element', $node);
			$names[] = $name;
		}
		$this->assertContains('filetransfer_ftp_username', $names);
		$this->assertContains('filetransfer_ftp_password', $names);
		$this->assertContains('filetransfer_ftp_host', $names);
		$this->assertContains('filetransfer_ftp_port', $names);
		$this->assertContains('filetransfer_ftp_remote_path', $names);
	}

	/**
	 * verify the configuration is read correctly
	 * @test
	 * @loadFixture ftpConfig
	 */
	public function testConfigValues()
	{
		$helper = $this->getHelperMock('core/data', array('decrypt'));
		$helper->expects($this->any())
			->method('decrypt')
			->with($this->identicalTo('0:2:3fbb7c95e7c84df0:ZA2CwPF1DexZjAOEXMLcxA=='))
			->will($this->returnValue('welcome1'));
		$this->replaceByMock('helper', 'core', $helper);
		$cfg = $this->getModelMockBuilder('filetransfer/protocol_config')
			->disableOriginalConstructor()
			->setMethods(array('none'))
			->getMock();
		$cfg->setData(self::$_config);
		$this->assertSame('testsection/testgroup', $cfg->getConfigPath());
		$this->assertSame('ftp', $cfg->getProtocolCode());
		$cfg->loadMappedFields(self::$_fieldMap);
		$this->assertSame('somename', $cfg->getUsername());
		$this->assertSame('welcome1', $cfg->getPassword());
		$this->assertSame('some.host', $cfg->getHost());
		$this->assertSame('21', $cfg->getPort());
		$this->assertSame('/', $cfg->getRemotePath());
	}

	/**
	 * verify getUrl generates a url using the data stored in the model.
	 * @test
	 * @dataProvider dataProvider
	 */
	public function testGetUrl($data, $includePath, $url)
	{

		$config = $this->getModelMockBuilder('filetransfer/protocol_config')
			->disableOriginalConstructor()
			->setMethods(array('none'))
			->getMock();
		$config->setData($data);
		$this->assertSame($url, $config->getUrl($includePath));
	}

	/**
	 * verify TrueAction_FileTransfer_Exception_Configuration is thrown when the protocol code doesn't match a known code.
	 * @test
	 */
	public function testConstructorException()
	{
		$helper = $this->getHelperMockBuilder('filetransfer/data')
			->disableOriginalConstructor()
			->setMethods(array('getProtocolCodes'))
			->getMock();
		$helper->expects($this->atLeastOnce())
			->method('getProtocolCodes')
			->will($this->returnValue(array()));
		$this->replaceByMock('helper', 'filetransfer', $helper);

		$testModel = $this->getModelMockBuilder('filetransfer/protocol_config')
			->disableOriginalConstructor()
			->setMethods(array('getProtocolCode', 'loadMappedFields'))
			->getMock();
		$testModel->expects($this->any())
			->method('loadMappedFields');
		$testModel->expects($this->atLeastOnce())
			->method('getProtocolCode')
			->will($this->returnValue(null));
		$this->setExpectedException(
			'TrueAction_FileTransfer_Exception_Configuration',
			'FileTransfer Config Error: Invalid Protocol Code'
		);
		$method = new ReflectionMethod($testModel, '_construct');
		$method->setAccessible(true);
		$method->invoke($testModel);
	}
}
