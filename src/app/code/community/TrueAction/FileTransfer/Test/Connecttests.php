<?php
class TrueAction_FileTransfer_Test_ConnectTests extends EcomDev_PHPUnit_Test_Case
{
	/**
	 * @test
	 * @loadFixture getfile.yaml
	 * */
	public function testConnectivity() {
		$result = Mage::helper('filetransfer')->getFile(
			'disclaimer.txt',
			'/tmp',
			'testsection/testgroup'
		);
		$this->assertTrue($result);
	}
}