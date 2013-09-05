<?php
/**
 * Unit Tests for Protocol/ Type/ Ftps
 *
 */
class TrueAction_FileTransfer_Test_Model_Protocol_Types_FtpsTest extends TrueAction_FileTransfer_Test_Abstract
{
	/**
	 * Mocks adapter to return OK on connect, covers the protocol type
	 *
	 * @test
	 */
	public function testConnectOk()
	{
		$this->replaceModel(
			'filetransfer/adapter_ftps',
			array (
				'ftpSslConnect' => true,
			)
		);
		$this->assertTrue(Mage::getModel('filetransfer/protocol_types_ftps')->connect());
	}

	/**
	 * Mocks adapter to return false on connect, covers the protocol type error handling
	 *
	 * @test
	 */
	public function testConnectFails()
	{
		$this->replaceModel(
			'filetransfer/adapter_ftps',
			array (
				'ftpSslConnect' => false,
			)
		);
		$this->assertFalse(Mage::getModel('filetransfer/protocol_types_ftps')->connect());
	}
}
