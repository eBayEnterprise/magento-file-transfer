<?php
class TrueAction_FileTransfer_Test_Model_Adapter_SftpTest extends EcomDev_PHPUnit_Test_Case
{
	const TESTBASE_DIR_NAME = 'testBase';
	const FILE1_NAME        = 'munsters.txt';
	const FILE1_CONTENTS    = 'The Munsters is an American television sitcom depicting the home life of a family of benign monsters.';
	const FILE2_NAME        = 'addams.txt';
	const FILE2_CONTENTS    = 'The Addams Family is an American television series based on the characters in Charles Addams';
	const FILE3_NAME        = 'gilligan.txt';
	const FILE3_CONTENTS    = 'Gilligan\'s Island is an American sitcom created and produced by Sherwood Schwartz.';

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
		$this->_adapter = Mage::getModel('filetransfer/adapter_sftp');
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
	 * Test ssh2Connect with port=0 will instead try port 22 
	 * @test
	 * The inverse of this test is to have
     * expectedException Exception
	 * and then look for
	 * expectedExceptionMessage localhost on port
	 */
	public function testSshConnectZeroPort()
	{
		$this->_adapter->ssh2Connect('localhost',0);
	}

	/**
	 * Test ssh2Connect with invalid host will throw Exception as expected
	 * @test
     * @expectedException Exception
	 * @expectedExceptionMessage getaddrinfo failed
	 */
	public function testSshConnectInvalidHost()
	{
		$this->_adapter->ssh2Connect('monkeyBusinessHostName',0);
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

	public function testDirectoryFunctions()
	{
		$dirHandle = $this->_adapter->opendir($this->_vfs->url(self::TESTBASE_DIR_NAME));
		$this->assertSame('stream', get_resource_type($dirHandle));
		$first = $this->_adapter->readdir($dirHandle);
		$second = $this->_adapter->readdir($dirHandle);
		$third = $this->_adapter->readdir($dirHandle);
		$term = $this->_adapter->readdir($dirHandle);
		$this->assertSame(self::FILE1_NAME, $first);
		$this->assertSame(self::FILE2_NAME, $second);
		$this->assertSame(self::FILE3_NAME, $third);
		$this->assertFalse($term);
		$this->assertNull($this->_adapter->closedir($dirHandle));
		try {
			$this->_adapter->readdir($dirHandle);
			// if the resource has been properly closed, attempting to use it again
			// should thrown an exception. If it doesn't, fail.
			$this->fail('Directory resource not closed.');
		} catch (Exception $e) {
		}
	}

	/**
	 * Test the isFile method.
	 *
	 * @test
	 */
	public function testIsFile()
	{
		$this->assertTrue($this->_adapter->isFile($this->_vfs->url(self::TESTBASE_DIR_NAME . '/' . self::FILE1_NAME)));
		$this->assertFalse($this->_adapter->isFile($this->_vfs->url(self::TESTBASE_DIR_NAME)));
	}

	/**
	 * Test the unlink method.
	 *
	 * @test
	 */
	public function testUnlinkFile()
	{
		$targetFile = $this->_vfs->url(self::TESTBASE_DIR_NAME . DS . self::FILE1_NAME);
		$this->assertTrue($this->_adapter->unlink($targetFile));
		$this->assertFalse(file_exists($targetFile));
	}
}
