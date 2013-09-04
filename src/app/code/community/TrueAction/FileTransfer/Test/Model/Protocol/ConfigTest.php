<?php
/*
TrueAction_FileTransfer_Test_Model_Config_Ftp

tests for the ftp config generator.
 */
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
		$this->loadMappedFields = $this->class->getMethod('loadMappedFields');
		$this->loadMappedFields->setAccessible(true);
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
	 * @test
	 * */
	public function testGenerateConfig()
	{
		$model = $this->class->newInstance(self::$_config);
		$config = $model->setConfigPath('testsection/testgroup')
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
	 * @test
	 * @loadFixture ftpConfig
	 * @doNotIndexAll
	 * */
	public function testConfigValues()
	{
		$cfg = $this->class->newInstance(self::$_config);
		$this->assertSame('testsection/testgroup', $cfg->getConfigPath());
		$this->assertSame('ftp', $cfg->getProtocolCode());
		$this->loadMappedFields->invoke($cfg, self::$_fieldMap);
		$this->assertSame('somename', $cfg->getUsername());
		$this->assertSame('welcome1', $cfg->getPassword());
		$this->assertSame('some.host', $cfg->getHost());
		$this->assertSame('21', $cfg->getPort());
		$this->assertSame('/', $cfg->getRemotePath());
	}
}
