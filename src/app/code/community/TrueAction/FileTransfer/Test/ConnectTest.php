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
class TrueAction_FileTransfer_Test_ConnectTest extends TrueAction_FileTransfer_Test_Abstract
{
	const TESTBASE_DIR_NAME = 'testBase';
	const FILE1_NAME        = 'munsters.txt';
	const FILE1_CONTENTS    = 'The Munsters is an American television sitcom depicting the home life of a family of benign monsters.';
	const FILE2_NAME        = 'addams.txt';

	const CHUNK_SIZE = 1024;

	private $_vfs;

	public function setUp()
	{
		$this->_vfs = $this->getFixture()->getVfs();
		$this->_vfs->apply(
			array(
				self::TESTBASE_DIR_NAME =>
				array (
					self::FILE1_NAME   => self::FILE1_CONTENTS,
					self::FILE2_NAME   => '',
				)
			)
		);
		$this->_vRemoteFile = $this->_vfs->url(self::TESTBASE_DIR_NAME . '/' . self::FILE1_NAME);
		$this->_vLocalFile  = $this->_vfs->url(self::TESTBASE_DIR_NAME . '/' . self::FILE2_NAME);
	}

	/**
	 * Test invalid_protocol calls, mocking the sftp adapter
	 *
	 * @test
	 * @expectedException Mage_Core_Exception
	 */
	public function testMikeSftpConnectivity()
	{
		$model = Mage::helper('filetransfer')->getProtocolModel(
			'testsection/testgroup',
			'SomeStrangeAndUnnaturalValueForAProtocol8D82507D585D579A01235E6A51288E0A1B186EED'
		);
	}

	/**
	 * Test sftp calls, mocking the sftp adapter
	 * 
	 * @test
	 */
	public function testSftpConnectivity()
	{
		// Simulate the low-level Adapter, and we can cover all the calls
		$this->replaceModel(
			'filetransfer/adapter_sftp',
			array (
				'fclose'             => true,
				'fread'              => self::FILE1_CONTENTS,
				'fopen'              => fopen($this->_vRemoteFile, 'wb+'),
				'fwrite'             => 100,
				'streamGetContents'  => self::FILE1_CONTENTS,
				'ssh2Connect'        => true,
				'ssh2Sftp'           => true,
				'ssh2AuthPubkeyFile' => true,
				'ssh2AuthPassword'   => true,
			)
		);

		$model = Mage::helper('filetransfer')->getProtocolModel(
			'testsection/testgroup',
			'sftp'
		);

		$this->assertTrue($model->sendString(self::FILE1_CONTENTS, $this->_vRemoteFile));
		$this->assertSame(self::FILE1_CONTENTS, $model->getString($this->_vRemoteFile));
		$this->assertTrue($model->getFile($this->_vLocalFile, $this->_vRemoteFile));
		$this->assertTrue($model->sendFile($this->_vLocalFile, $this->_vRemoteFile));
	}

	/**
	 * Xtest
	 * @loadFixture connectSettings.yaml
	 */
	public function XtestHelperConnectivity()
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
	 * Xtest
	 * @loadFixture testSftpKey
	 */
	public function XtestSftpKey()
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
