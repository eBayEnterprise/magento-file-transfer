<?php
class TrueAction_FileTransfer_Test_ConnectTests extends EcomDev_PHPUnit_Test_Case
{
	/**
	 * @test
	 * @loadFixture getfile
	 * */
	public function testGetFile() {
		$result = Mage::helper('filetransfer')->getFile(
			'ubuntu-archive-keyring.gpg',
			'/tmp/foo.txt',
			'testsection/testgroup'
		);
		$this->assertTrue($result);
	}

	/**
	 * @test
	 * @loadFixture sendfile
	 * */
	public function testSendFile() {
		$result = Mage::helper('filetransfer')->sendFile(
			'ubuntu-archive-keyring.gpg',
			'/tmp/foo.txt',
			'testsection/testgroup'
		);
		$this->assertTrue($result);
	}

	/**
	 * @test
	 * @loadFixture sendfile
	 * */
	public function testSendString() {
		$result = Mage::helper('filetransfer')->sendString(
			',,,,,',
			'3471_ftransfer_test.csv',
			'testsection/testgroup'
		);
		$this->assertTrue($result);
	}
}