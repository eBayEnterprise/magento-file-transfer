<?php
/**
 * these tests aren't unit tests (by definition) as they need external stuff
 * setup. as such these tests should not run by default and have to be run
 * manually.
 *
 * NOTE:
 * for these tests to work you must have a server listening on the localhost for
 * each protocol tested. a user must be setup as follows:
 *
 * username: test
 * password: welcome1
 *
 * NOTE:
 * the sftp test uses the keys included in fixtures/opensshkeys.
 */
class TrueAction_FileTransfer_Test_ConnectTest extends EcomDev_PHPUnit_Test_Case
{
	public function setUp()
	{
		@unlink('/tmp/foo.txt');
		@unlink('/tmp/3471_ftransfer_test.csv');
		@unlink('/tmp/3471_ftransfer_test2.csv');
	}

	/**
	 * @test
	 * @loadFixture connectSettings.yaml
	 * @dataProvider dataProvider
	 */
	public function testConnectivity($protocol)
	{
		$model = Mage::helper('filetransfer')->getProtocolModel(
			'testsection/testgroup',
			$protocol
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

	/**
	 * @test
	 * @loadFixture connectSettings.yaml
	 */
	public function testHelperConnectivity()
	{
		$helper = Mage::helper('filetransfer');
		$configPath = 'testsection/testgroup';
		$result = $helper->sendString(',,,,,', '3471_ftransfer_test.csv', $configPath);
		$this->assertTrue($result);

		$result = $helper->getString('3471_ftransfer_test.csv', $configPath);
		$this->assertSame(',,,,,', $result);

		$result = $helper->getFile(
			'/tmp/foo.txt',
			'3471_ftransfer_test.csv',
			$configPath
		);
		$this->assertTrue($result);

		$result = $helper->sendFile(
			'/tmp/foo.txt',
			'3471_ftransfer_test2.csv',
			$configPath
		);
		$this->assertTrue($result);
	}

	/**
	 * @test
	 * @loadFixture testSftpKey
	 */
	public function testSftpKey()
	{
		$dir = dirname(__FILE__) . '/ConnectTests/fixtures';
		$model = Mage::helper('filetransfer')->getProtocolModel(
			'testsection/testgroup',
			'sftp'
		);
		$config = $model->getConfig();
		$pub = $dir . $config->getPublicKey();
		$prv = $dir . $config->getPrivateKey();
		$config->setPublicKey($pub)
			->setPrivateKey($prv);
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
