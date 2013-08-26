<?php
/*
TrueAction_FileTransfer_Test_Model_Config_Ftp

tests for the ftp config generator.
 */
class TrueAction_FileTransfer_Test_Model_Protocol_AbstractTest extends EcomDev_PHPUnit_Test_Case
{
	public function setUp()
	{
		$this->cls = new ReflectionClass(
			'TrueAction_FileTransfer_Model_Protocol_Abstract'
		);
		$this->getCodes = $this->cls->getMethod(
			'getCodes'
		);
	}


	/**
	 * @test
	 * */
	public function testListProtocols()
	{
		$ls = $this->getCodes->invoke(null);
		$this->assertContains('ftp', $ls);
		$this->assertContains('sftp', $ls);
		$this->assertNotContains('.', $ls);
		$this->assertNotContains('..', $ls);
		$ls2 = $this->getCodes->invoke(null);
		$this->assertSame($ls, $ls2);
	}
}
