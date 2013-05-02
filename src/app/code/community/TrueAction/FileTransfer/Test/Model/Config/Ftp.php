<?php
/*
TrueAction_FileTransfer_Test_Model_Config_Ftp

tests for the ftp config generator.
 */
class TrueAction_FileTransfer_Test_Model_Config_Ftp
	extends EcomDev_PHPUnit_Test_Case
{
	public function setUp()
	{
		$this->class = new ReflectionClass(
			'TrueAction_FileTransfer_Model_Config_Ftp'
		);
	}

	/**
	 * @test
	 * */
	public function testGetConfig() {
		$model = $this->class->newInstance();
		$importOptions = null;
		$config = $model->getConfig($importOptions);
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
}
