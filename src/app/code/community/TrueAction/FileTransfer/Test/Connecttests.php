<?php
/**
 * these tests aren't unit tests (by definition) as they need external stuff
 * setup. as such these tests should not run by default and have to be run
 * manually.
 * */
class TrueAction_FileTransfer_Test_ConnectTests extends EcomDev_PHPUnit_Test_Case
{
	public function setUp()
	{
		@unlink('/tmp/foo.txt');
	}

	/**
	 * @test
	 * @loadFixture sendfile
	 * */
	public function runTest() {
		$model = Mage::helper('filetransfer')->getProtocolModel(
			'testsection/testgroup',
			'sftp'
		);
		$result = $model->sendString(',,,,,', '3471_ftransfer_test.csv');
		$this->assertTrue($result);

		$result = $model->getString('3471_ftransfer_test.csv');
		$this->assertSame(',,,,,', $result);

		$result = $model->getFile(
			'/tmp/foo.txt',
			'3471_ftransfer_test.csv'
		);
		$this->assertTrue($result);

		$result = $model->sendFile(
			'/tmp/foo.txt',
			'3471_ftransfer_test2.csv'
		);
		$this->assertTrue($result);
	}
}