<?php
class TrueAction_FileTransfer_Test_Model_Protocol_Types_Sftp_ConfigTest extends EcomDev_PHPUnit_Test_Case
{
	private static $_config = array(
		'config_path' => 'testsection/testgroup',
		'protocol_code' => 'sftp',
	);

	private $_fieldMap = array(
		'filetransfer_sftp_ssh_prv_key' => 'private_key',
		'filetransfer_sftp_auth_type'   => 'auth_type',
		'filetransfer_sftp_username'    => 'username',
		'filetransfer_sftp_password'    => 'password',
		'filetransfer_sftp_host'        => 'host',
		'filetransfer_sftp_port'        => 'port',
		'filetransfer_sftp_remote_path' => 'remote_path',
	);

	public function setUp()
	{
		$this->class = new ReflectionClass(
			'TrueAction_FileTransfer_Model_Protocol_Types_Sftp_Config'
		);
		$this->loadMappedFields = $this->class->getMethod('loadMappedFields');
		$this->loadMappedFields->setAccessible(true);
		$this->importOptions = new Varien_Simplexml_Config(
			'<filetransfer>
				<sort_order>190</sort_order>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
				<sftp></sftp>
			</filetransfer>'
		);
	}
	/**
	 * Ensure the private key is decrypted and reformatted.
	 * @test
	 * @loadFixture privKeyConfig
	 * @doNotIndexAll
	 */
	public function testPrivateKeyEncryption()
	{
		$helper = $this->getHelperMock('core/data', array('encrypt', 'decrypt'));
		$helper->expects($this->once())
			->method('encrypt')
			->will($this->returnValue('encrypted private key'));
		$helper->expects($this->once())
			->method('decrypt')
			->with($this->identicalTo('encrypted private key in config'))
			->will($this->returnValue('-----BEGIN RSA PRIVATE KEY----- 0000000000000000000000000000000000000000000000000000000000000000 1111111111111111111111111111111111111111111111111111111111111111 0000000000000000000000000000000000000000000000000000000000000000 1111111111111111111111111111111111111111111111111111111111111111 00000000000000000000000000000000000000000000000000000000000 -----END RSA PRIVATE KEY-----'));
		$this->replaceByMock('helper', 'core', $helper);
		$config = $this->getModelMockBuilder('filetransfer/protocol_types_sftp_config')
			->disableOriginalConstructor()
			->setMethods(array('none'))
			->getMock();
		$config->setData(self::$_config)
			->loadMappedFields($this->_fieldMap);
		$this->assertSame('-----BEGIN RSA PRIVATE KEY-----
0000000000000000000000000000000000000000000000000000000000000000
1111111111111111111111111111111111111111111111111111111111111111
0000000000000000000000000000000000000000000000000000000000000000
1111111111111111111111111111111111111111111111111111111111111111
00000000000000000000000000000000000000000000000000000000000
-----END RSA PRIVATE KEY-----', $config->getPrivateKey());
		$config->setPrivateKey('dectypted private key');
		$this->assertSame('encrypted private key', $config->getData('private_key'));
	}
	/**
	 * Make sure the reformatting works with an already formatted string.
	 * @test
	 * @loadFixture privKeyConfig
	 * @doNotIndexAll
	 * */
	public function testPrivateKeyEncryptionPreFormatted()
	{
		$helper = $this->getHelperMock('core/data', array('encrypt', 'decrypt'));
		$helper->expects($this->once())
			->method('encrypt')
			->will($this->returnValue('encrypted private key'));
		$helper->expects($this->once())
			->method('decrypt')
			->with($this->identicalTo('encrypted private key in config'))
			->will($this->returnValue('-----BEGIN RSA PRIVATE KEY-----
0000000000000000000000000000000000000000000000000000000000000000
1111111111111111111111111111111111111111111111111111111111111111
0000000000000000000000000000000000000000000000000000000000000000
1111111111111111111111111111111111111111111111111111111111111111
00000000000000000000000000000000000000000000000000000000000
-----END RSA PRIVATE KEY-----'));
		$this->replaceByMock('helper', 'core', $helper);
		$config = $this->getModelMockBuilder('filetransfer/protocol_types_sftp_config')
			->disableOriginalConstructor()
			->setMethods(array('none'))
			->getMock();
		$config->setData(self::$_config)
			->loadMappedFields($this->_fieldMap);
		$this->assertSame('-----BEGIN RSA PRIVATE KEY-----
0000000000000000000000000000000000000000000000000000000000000000
1111111111111111111111111111111111111111111111111111111111111111
0000000000000000000000000000000000000000000000000000000000000000
1111111111111111111111111111111111111111111111111111111111111111
00000000000000000000000000000000000000000000000000000000000
-----END RSA PRIVATE KEY-----', $config->getPrivateKey());
		$config->setPrivateKey('dectypted private key');
		$this->assertSame('encrypted private key', $config->getData('private_key'));
	}

	/**
	 * verify the constructor sets the protocol code for the model.
	 * @test
	 */
	public function testConstructorProtocolCode()
	{
		$config = $this->getModelMockBuilder('filetransfer/protocol_types_sftp_config')
			->disableOriginalConstructor()
			->setMethods(array('setProtocolCode', '_validateProtocolCode'))
			->getMock();
		$config->expects($this->once())
			->method('setProtocolCode')
			->with($this->identicalTo('sftp'))
			->will($this->returnSelf());
		EcomDev_Utils_Reflection::invokeRestrictedMethod($config, '_construct');
	}

	/**
	 * verify the base fields are generated as expected.
	 * @test
	 */
	public function testGetBaseFields()
	{
		$config = $this->getModelMockBuilder('filetransfer/protocol_types_sftp_config')
			->disableOriginalConstructor()
			->setMethods(array('getProtocolCode'))
			->getMock();
		$config->expects($this->once())
			->method('getProtocolCode')
			->will($this->returnValue('foo'));

		$result = $config->getBaseFields();
		$this->assertInstanceOf('Varien_Simplexml_Config', $result);

		$resultNode = $result->getNode();
		$this->assertSame('fields', $resultNode->getName());

		$paths = array(
			'filetransfer_protocol/label[.="Protocol"]',
		);
		foreach ($paths as $path) {
			$this->assertNotEmpty(
				$resultNode->xpath($path),
				"path: '$path'"
			);
		}
	}
}
