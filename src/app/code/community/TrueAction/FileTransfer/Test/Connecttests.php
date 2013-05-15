<?php
class TrueAction_FileTransfer_Test_ConnectTests extends EcomDev_PHPUnit_Test_Case
{
	/**
	 * @test
	 * @loadFixture getfile
	 * */
	public function testConnectivity() {
		$result = Mage::helper('filetransfer')->getFile(
			'ubuntu-archive-keyring.gpg',
			'/tmp/foo.txt',
			'testsection/testgroup'
		);
		$this->assertTrue($result);
	}
}