<?php
class TrueAction_FileTransfer_Test_Model_Adapter_SftpTest extends EcomDev_PHPUnit_Test_Case
{
	const TESTBASE_DIR_NAME = 'testBase';
	const FILE1_NAME        = 'munsters.txt';
	const FILE1_CONTENTS    = 'The Munsters is an American television sitcom depicting the home life of a family of benign monsters.';
	const FILE2_NAME        = 'addams.txt';
	const FILE2_CONTENTS    = 'The Addams Family is an American television series based on the characters in Charles Addams';
	const FILE3_NAME        = 'gilligan.txt';
	const FILE3_CONTENTS	= 'Gilligan\'s Island is an American sitcom created and produced by Sherwood Schwartz.';

	const CHUNK_SIZE = 1024;

	private $_adapter;
	private $_vfs;

	public function setUp()
	{
		$this->_vfs = $this->getFixture()->getVfs();
		$this->_vfs->apply(
			array(
				self::TESTBASE_DIR_NAME =>
				array (
					self::FILE1_NAME   => self::FILE1_CONTENTS,
					self::FILE2_NAME   => self::FILE2_CONTENTS,
					self::FILE3_NAME   => '',
				)
			)
		);
		$this->_adapter = new TrueAction_FileTransfer_Model_Adapter_Sftp();
	}

	/**
	 * Make sure _adapter really is the right object
	 *
	 * @test
	 */
	public function testNewObject()
	{
		$this->assertInstanceOf('TrueAction_FileTransfer_Model_Adapter_Sftp', $this->_adapter);
	}

	/**
	 * fopen, use streamGetContents to get contents, and fclose
	 *
	 * @test
	 */
	public function testStreamGetContents()
	{
		$fp = $this->_adapter->fopen($this->_vfs->url(self::TESTBASE_DIR_NAME . '/' . self::FILE1_NAME), 'r');
		$this->assertNotSame(false, $fp);

		$contents = $this->_adapter->streamGetContents($fp);
		$this->assertStringStartsWith(self::FILE1_CONTENTS, $contents);

		$rc = $this->_adapter->fclose($fp);
		$this->assertSame(true, $rc);
	}

	/**
	 * Here we fopen and use fread to get contents, and naturally we fclose
	 * 
	 * @test
	 */
	public function testFread()
	{
		$fp = $this->_adapter->fopen($this->_vfs->url(self::TESTBASE_DIR_NAME . '/' . self::FILE2_NAME), 'rb');
		$this->assertNotSame(false, $fp);

		$contents = $this->_adapter->fread($fp, self::CHUNK_SIZE);
		$this->assertStringStartsWith(self::FILE2_CONTENTS, $contents);

		$rc = $this->_adapter->fclose($fp);
		$this->assertSame(true, $rc);
	}

	/**
	 * Here we fopen and use fwrite to put some contents, and naturally we fclose
	 * 
	 * @test
	 */
	public function testFwrite()
	{
		$fp = $this->_adapter->fopen($this->_vfs->url(self::TESTBASE_DIR_NAME . '/' . self::FILE3_NAME), 'w+b');
		$this->assertNotSame(false, $fp);

		// The file should be empty:
		$contents = $this->_adapter->fread($fp, self::CHUNK_SIZE);
		$this->assertEquals('', $contents);

		// Let's put content into the file:
		$contentLength = strlen(self::FILE3_CONTENTS);
		$rc = $this->_adapter->fwrite($fp, self::FILE3_CONTENTS, $contentLength);
		$this->assertEquals($contentLength, $rc);

		$rc = $this->_adapter->fclose($fp);
		$this->assertSame(true, $rc);
	}

	/**
	 * Coverage for ssh2Connect 
	 *
	 * @test
     * @expectedException Exception
     */
	public function testSshConnect()
	{
		$this->_adapter->ssh2Connect(null, 0);
	}

	/**
	 * Coverage for ssh2Sftp
	 *
	 * @test
     * @expectedException Exception
	 */
	public function testSshSftp()
	{
		$this->_adapter->ssh2Sftp(null);
	}

	/**
	 * Coverage for ssh2AuthPubkeyFile
	 *
	 * @test
     * @expectedException Exception
	 */
	public function testSshAuthPubkeyFile()
	{
		$this->_adapter->ssh2AuthPubkeyFile(null, null, null, null);
	}

	/**
	 * Coverage for ssh2_auth_password
	 *
	 * @test
     * @expectedException Exception
	 */
	public function testSshAuthPassword()
	{
		$this->_adapter->ssh2AuthPassword(null, null, null);
	}
}
