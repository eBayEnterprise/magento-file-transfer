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
	/**
	 * @test
	 */
	public function testGetBaseFields()
	{
		$config = $this->getModelMockBuilder('filetransfer/protocol_config')
			->disableOriginalConstructor()
			->setMethods(array('getProtocolCode'))
			->getMock();
		$config->expects($this->once())
			->method('getProtocolCode')
			->will($this->returnValue('SFTP'));

		$fields = $config->getBaseFields();

		$this->assertInstanceOf('Varien_Simplexml_Config', $fields);

		$this->assertSame(
			preg_replace('/[ ]{2,}|[\t]/', '', str_replace(array("\r\n", "\r", "\n"), '',
			'<fields>
				<filetransfer_protocol translate="label">
				<label>Protocol</label>
				<frontend_type>select</frontend_type>
				<source_model>filetransfer/adminhtml_system_config_source_protocols</source_model></filetransfer_protocol>
				<filetransfer_SFTP_username translate="label">
					<label>Username</label>
					<frontend_type>text</frontend_type>
					<depends>
						<filetransfer_protocol>SFTP</filetransfer_protocol>
					</depends>
				</filetransfer_SFTP_username>
				<filetransfer_SFTP_password translate="label">
					<label>Password</label>
					<frontend_type>obscure</frontend_type>
					<backend_model>adminhtml/system_config_backend_encrypted</backend_model>
					<depends>
						<filetransfer_protocol>SFTP</filetransfer_protocol>
					</depends>
				</filetransfer_SFTP_password>
				<filetransfer_SFTP_host translate="label">
					<label>Remote Host</label>
					<frontend_type>text</frontend_type>
					<depends>
						<filetransfer_protocol>SFTP</filetransfer_protocol>
					</depends>
				</filetransfer_SFTP_host>
				<filetransfer_SFTP_port translate="label">
					<label>Remote Port</label>
					<frontend_type>text</frontend_type>
					<depends>
						<filetransfer_protocol>SFTP</filetransfer_protocol>
					</depends>
				</filetransfer_SFTP_port>
				<filetransfer_SFTP_remote_path translate="label">
					<label>Remote Path</label>
					<frontend_type>text</frontend_type>
					<depends>
						<filetransfer_protocol>SFTP</filetransfer_protocol>
					</depends>
				</filetransfer_SFTP_remote_path>
			</fields>'
			)),
			$fields->getXmlString()
		);
	}
	/**
	 * verify the protocol code check wont fail when the desired code is at index 0 in the array.
	 * @test
	 */
	public function testConstructorProtocolCheck()
	{
		$helper = $this->getHelperMock('filetransfer/data', array('getProtocolCodes'));
		$helper->expects($this->once())
			->method('getProtocolCodes')
			->will($this->returnValue(array( 0 => 'the_code')));
		$this->replaceByMock('helper', 'filetransfer', $helper);
		$config = $this->getModelMockBuilder('filetransfer/protocol_config')
			->disableOriginalConstructor()
			->setMethods(array('getProtocolCode'))
			->getMock();
		$config->expects($this->atLeastOnce())
			->method('getProtocolCode')
			->will($this->returnValue('the_code'));
		$ctor = new ReflectionMethod($config, '_construct');
		$ctor->setAccessible(true);
		$ctor->invoke($config);
	}
}
