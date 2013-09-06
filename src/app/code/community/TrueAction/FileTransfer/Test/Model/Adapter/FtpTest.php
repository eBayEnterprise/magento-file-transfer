<?php
class TrueAction_FileTransfer_Test_Model_Adapter_FtpTest extends EcomDev_PHPUnit_Test_Case
{
	const TESTBASE_DIR_NAME = 'testBase';
	const FILE1_NAME        = 'munsters.txt';
	const FILE1_CONTENTS    = 'The Munsters is an American television sitcom depicting the home life of a family of benign monsters.';
	const FILE2_NAME        = 'addams.txt';
	const FILE2_CONTENTS    = 'The Addams Family is an American television series based on the characters in Charles Addams';
	const FILE3_NAME        = 'gilligan.txt';
	const FILE3_CONTENTS	= 'Gilligan\'s Island is an American sitcom created and produced by Sherwood Schwartz.';

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
		$this->_adapter = Mage::getModel('filetransfer/adapter_ftp');
	}

	/**
	 * Make sure _adapter really is the right object
	 *
	 * @test
	 */
	public function testNewObject()
	{
		$this->assertInstanceOf('TrueAction_FileTransfer_Model_Adapter_Ftp', $this->_adapter);
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
	 * ftpConnect test
	 *
	 * @test
	 * @expectedException Exception
	 */
	public function testFtpConnect()
	{
		$this->_adapter->ftpConnect(null);
	}

	/**
	 * ftpClose test
	 *
	 * @test
	 * @expectedException Exception
	 */
	public function testFtpClose()
	{
		$this->_adapter->ftpClose(null);
	}

	/**
	 * ftpFget test
	 *
	 * @test
	 * @expectedException Exception
	 */
	public function testFtpFget()
	{
		$this->_adapter->ftpFget(null, null, null, null);
	}

	/**
	 * ftpFput test
	 *
	 * @test
	 * @expectedException Exception
	 */
	public function testFtpFput()
	{
		$this->_adapter->ftpFput(null, null, null, null);
	}

	/**
	 * ftpLogin test
	 *
	 * @test
	 * @expectedException Exception
	 */
	public function testFtpLogin()
	{
		$this->_adapter->ftpLogin(null, null, null);
	}

	/**
	 * ftpPasv test
	 *
	 * @test
	 * @expectedException Exception
	 */
	public function testFtpPasv()
	{
		$this->_adapter->ftpPasv(null, true);
	}
}
