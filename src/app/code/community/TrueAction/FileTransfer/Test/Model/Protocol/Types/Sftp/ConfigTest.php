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
	public function testGetBaseFields()
	{
		$config = $this->getModelMockBuilder('filetransfer/protocol_types_sftp_config')
			->disableOriginalConstructor()
			->setMethods(array('getProtocolCode'))
			->getMock();
		$config->expects($this->once())
			->method('getProtocolCode')
			->will($this->returnValue('sftp'));

		$this->assertSame(
			'<fields><filetransfer_protocol translate="label"><label>Protocol</label><frontend_type>select</frontend_type><source_model>filetransfer/adminhtml_system_config_source_protocols</source_model></filetransfer_protocol><filetransfer_sftp_auth_type translate="label"><label>Authentication Method</label><frontend_type>select</frontend_type><source_model>filetransfer/adminhtml_system_config_source_Authtypes</source_model><depends><filetransfer_protocol>sftp</filetransfer_protocol></depends></filetransfer_sftp_auth_type><filetransfer_sftp_username translate="label"><label>Username</label><frontend_type>text</frontend_type><depends><filetransfer_protocol>sftp</filetransfer_protocol></depends></filetransfer_sftp_username><filetransfer_sftp_password translate="label"><label>Password</label><frontend_type>obscure</frontend_type><backend_model>adminhtml/system_config_backend_encrypted</backend_model><depends><filetransfer_protocol>sftp</filetransfer_protocol><filetransfer_sftp_auth_type>password</filetransfer_sftp_auth_type></depends></filetransfer_sftp_password><filetransfer_sftp_ssh_prv_key translate="label"><label>SSH Private Key</label><frontend_type>obscure</frontend_type><backend_model>adminhtml/system_config_backend_encrypted</backend_model><depends><filetransfer_protocol>sftp</filetransfer_protocol><filetransfer_sftp_auth_type>pub_key</filetransfer_sftp_auth_type></depends></filetransfer_sftp_ssh_prv_key><filetransfer_sftp_host translate="label"><label>Remote Host</label><frontend_type>text</frontend_type><depends><filetransfer_protocol>sftp</filetransfer_protocol></depends></filetransfer_sftp_host><filetransfer_sftp_port translate="label"><label>Remote Port</label><frontend_type>text</frontend_type><depends><filetransfer_protocol>sftp</filetransfer_protocol></depends></filetransfer_sftp_port><filetransfer_sftp_remote_path translate="label"><label>Remote Path</label><frontend_type>text</frontend_type><depends><filetransfer_protocol>sftp</filetransfer_protocol></depends></filetransfer_sftp_remote_path></fields>',
			$config->getBaseFields()->getXmlString()
		);
	}
}
