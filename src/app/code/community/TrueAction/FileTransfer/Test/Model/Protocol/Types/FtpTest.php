<?php
/**
 * Unit Tests for Protocol/ Type/ Ftp
 *
 * @todo: THIS TEST SUITE INCOMPLETE
 */
class TrueAction_FileTransfer_Test_Model_Protocol_Types_FtpTest extends TrueAction_FileTransfer_Test_Abstract
{
	const TESTBASE_DIR_NAME = 'testBase';
	const FILE1_NAME        = 'munsters.txt';
	const FILE1_CONTENTS    = 'The Munsters is an American television sitcom depicting the home life of a family of benign monsters.';
	const FILE2_NAME        = 'addams.txt';

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
	 * Test ftp call, mocking the ftp adapter
	 * 
	 * @test
	 */
	public function testFtpConnectivity()
	{

		// Simulate the ftp Adapter
		$this->replaceModel(
			'filetransfer/adapter_ftp',
			array (
				'ftpLogin'           => false,
				'ftpClose'           => true,
				'fclose'             => true,
				'fopen'              => fopen($this->_vRemoteFile, 'wb+'),
				'streamGetContents'  => self::FILE1_CONTENTS,
				'ftpConnect'         => true,
			)
		);

		$model = Mage::getModel('filetransfer/protocol_types_ftp');

		// Setting the port just to cover it and see that it chains
		$this->assertFalse($model->setPort(87)->sendString(self::FILE1_CONTENTS, $this->_vRemoteFile));
	}
}
