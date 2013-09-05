<?php
class TrueAction_FileTransfer_Test_Model_Adapter_FtpsTest extends EcomDev_PHPUnit_Test_Case
{
	private $_adapter;

	public function setUp()
	{
		$this->_adapter = Mage::getModel('filetransfer/adapter_ftps');
	}

	/**
	 * Coverage for ftp_ssl_connect
	 *
	 * @test
     * @expectedException Exception
	 */
	public function testftpSslConnect()
	{
		$this->_adapter->ftpSslConnect(null);
	}
}
