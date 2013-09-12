<?php

class TrueAction_FileTransfer_Test_Model_Protocol_Types_Sftp_ConfigTest
	extends EcomDev_PHPUnit_Test_Case
{

	private static $_config = array(
		'protocol_code' => 'sftp',
		'config_path' => 'testsection/testgroup'
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
	 * @test
	 * @loadFixture privKeyConfig
	 * @doNotIndexAll
	 * */
	public function testPrivateKeyEncryption()
	{
		$helper = $this->getHelperMock('core/data', array('encrypt', 'decrypt'));
		$helper->expects($this->once())
			->method('encrypt')
			->will($this->returnValue('encrypted private key'));
		$helper->expects($this->once())
			->method('decrypt')
			->with($this->identicalTo('encrypted private key in config'))
			->will($this->returnValue('decrypted private key'));
		$this->replaceByMock('helper', 'core', $helper);

		$config = Mage::getModel('filetransfer/protocol_types_sftp_config', self::$_config);
		$this->assertSame('decrypted private key', $config->getPrivateKey());
		$config->setPrivateKey('dectypted private key');
		$this->assertSame('encrypted private key', $config->getData('private_key'));
	}

}