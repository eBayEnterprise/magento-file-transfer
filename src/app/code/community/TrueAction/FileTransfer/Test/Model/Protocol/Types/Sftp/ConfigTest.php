<?php
class TrueAction_FileTransfer_Test_Model_Protocol_Types_Sftp_ConfigTest extends EcomDev_PHPUnit_Test_Case
{
	private static $_config = array(
		'config_path' => 'testsection/testgroup',
		'protocol_code' => 'sftp',
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
		$config = Mage::getModel('filetransfer/protocol_types_sftp_config', self::$_config);
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
		$config = Mage::getModel('filetransfer/protocol_types_sftp_config', self::$_config);
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
		$this->assertNotEmpty(
			$resultNode->xpath('filetransfer_protocol/label[.="Protocol"]')
		);
		$this->assertNotEmpty(
			$resultNode->xpath('filetransfer_foo_password/validate[.="required-entry"]')
		);
		$this->assertEmpty(
			$resultNode->xpath('filetransfer_foo_ssh_prv_key/validate[.="required-entry"]')
		);
		$this->assertNotEmpty(
			$resultNode->xpath('filetransfer_foo_ssh_prv_key/label[.="Upload Private Key"]')
		);
		$this->assertNotEmpty(
			$resultNode->xpath('filetransfer_foo_ssh_prv_key/backend_model[.="filetransfer/adminhtml_system_config_backend_encrypted_keyfile"]')
		);
		$this->assertNotEmpty(
			$resultNode->xpath('filetransfer_foo_ssh_prv_key/frontend_type[.="file"]')
		);
	}
}
