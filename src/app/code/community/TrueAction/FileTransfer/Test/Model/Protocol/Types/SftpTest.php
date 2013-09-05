<?php
/**
 *
 */
class TrueAction_FileTransfer_Test_Model_Protocol_Types_SftpTest extends TrueAction_FileTransfer_Test_Abstract
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
	 * Test sftp calls, mocking the sftp adapter
	 * 
	 * @todo move Config tests to TrueAction_FileTransfer_Model_Protocol_Types_Sftp_Config Unit Test (when it pops into existence)
	 * @test
	 */
	public function testSftpConnectivity()
	{

		// This is the key to testing here - we simulate the low-level Adapter, and we can cover all the calls
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

		$model = Mage::getModel('filetransfer/protocol_types_sftp');


		// Setting the port just to cover it and see that it chains
		$this->assertTrue($model->setPort(87)->sendString(self::FILE1_CONTENTS, $this->_vRemoteFile));

		$this->assertSame(self::FILE1_CONTENTS, $model->getString($this->_vRemoteFile));

		$this->assertTrue($model->getFile($this->_vLocalFile, $this->_vRemoteFile));

		$this->assertTrue($model->sendFile($this->_vLocalFile, $this->_vRemoteFile));

		// Sort this into proper Config Unit Test:
		$this->assertInstanceOf(
			'Varien_Simplexml_Config',
			$model->getConfig()
				->setPassword('justToCoverPassword')
				->getBaseFields()
		);
	}

	/**
	 * Test we can get remote, but not write locally
	 * Test we can get locally, but not write remote
	 * 
	 * @test
	 */
	public function testSftpFwriteFails()
	{

		// Simulate the low-level Adapter that fails on fwrite
		$this->replaceModel(
			'filetransfer/adapter_sftp',
			array (
				'fclose'             => true,
				'fread'              => self::FILE1_CONTENTS,
				'fopen'              => fopen($this->_vLocalFile, 'wb+'),
				'fwrite'             => false,
				'streamGetContents'  => self::FILE1_CONTENTS,
				'ssh2Connect'        => true,
				'ssh2Sftp'           => true,
				'ssh2AuthPubkeyFile' => true,
				'ssh2AuthPassword'   => true,
			)
		);

		$model = Mage::getModel('filetransfer/protocol_types_sftp');

		$this->assertSame(self::FILE1_CONTENTS, $model->getString($this->_vRemoteFile));

		$this->assertFalse($model->sendFile($this->_vLocalFile, $this->_vRemoteFile));
	}

	/**
	 * Force some failures to complete coverage
	 * 
	 * @test
	 */
	public function testSftpConnectFail()
	{
		// Force some low-level adapter failures
		$this->replaceModel(
			'filetransfer/adapter_sftp',
			array (
				'ssh2Connect'        => false, // Force 'connect()' to fail
				'ssh2Sftp'           => false, // Force 'initSftp()' to fail
				'fopen'              => false, // Force 'retrieve()' to fail
				'fclose'             => true,  // Just don't complain on fclose
				'ssh2AuthPassword'   => false, // Force 'login()' to fail
				'ssh2AuthPubkeyFile' => false, // Force 'login()' to fail when 'pub_key' configured
			)
		);
		$a = $b = 'foo';

		$model = Mage::getModel('filetransfer/protocol_types_sftp');
		$this->assertFalse( $model->connect() );
		$this->assertFalse( $model->initSftp() );
		$this->assertFalse( $model->retrieve($a, $b) );
		$this->assertFalse( $model->transfer($a, $b) );

		$this->assertFalse( $model->login() );

		$model->getConfig()->setAuthType('pub_key');	
		$this->assertFalse( $model->login() );
	}
}
