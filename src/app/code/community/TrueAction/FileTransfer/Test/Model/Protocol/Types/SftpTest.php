<?php
/**
 * Unit Tests for Protocol/ Type/ Sftp
 *
 */
class TrueAction_FileTransfer_Test_Model_Protocol_Types_SftpTest extends TrueAction_FileTransfer_Test_Abstract
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
			$model->getConfig()->setPassword('justToCoverPassword')->getBaseFields()
		);
	}

	/**
	 * Test sftp calls, mocking the sftp adapter
	 * @test
	 * @dataProvider dataProvider
	 */
	public function testSftpConnectivityFail($method, $expectedMessage)
	{
		$this->setExpectedException('TrueAction_FileTransfer_Exception_Transfer', $expectedMessage);
		// This is the key to testing here - we simulate the low-level Adapter, and we can cover all the calls
		$this->replaceModel(
			'filetransfer/adapter_sftp',
			array (
				'fopen' => false,
			)
		);
		$methods = array('connect', 'login', 'initSftp');
		$model = $this->getModelMock('filetransfer/protocol_types_sftp', $methods);
		foreach ($methods as $mockedMethod) {
			$model->expects($this->any())
				->method($mockedMethod)
				->will($this->returnValue(true));
		}
		$model->$method('somelocalfile', 'someremotefile');
	}

	/**
	 * Test we can get remote, but not write locally
	 * Test we can get locally, but not write remote
	 *
	 * @test
	 */
	public function testSftpFwriteFails()
	{
		$this->setExpectedException('TrueAction_FileTransfer_Exception_Transfer', 'the://url/ transfer error: Failed to write /vfs:/testBase/munsters.txt to the local system');
		// Simulate the low-level Adapter that fails on fwrite
		$config = $this->getModelMock('filetransfer/protocol_types_sftp_config', array('getUrl'));
		$config->expects($this->any())
			->method('getUrl')
			->will($this->returnValue('the://url/'));
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
		$model->setConfig($config);

		$this->assertSame(self::FILE1_CONTENTS, $model->getString($this->_vRemoteFile));

		$this->assertFalse($model->sendFile($this->_vLocalFile, $this->_vRemoteFile));
	}

	/**
	 * Force some failures to complete coverage
	 *
	 * @test
	 * @dataProvider dataProvider
	 */
	public function testSftpConnectionFail($method, $mockedMethods, $expectedMessage)
	{
		$this->setExpectedException('TrueAction_FileTransfer_Exception_Connection', $expectedMessage);
		// Force some low-level adapter failures
		$this->replaceModel('filetransfer/adapter_sftp', $mockedMethods);

		$model = Mage::getModel('filetransfer/protocol_types_sftp');

		$config = $this->getModelMock('filetransfer/protocol_types_sftp_config', array('getUrl'));
		$configData = $model->getConfig()
			->getData();
		$config->expects($this->any())
			->method('getUrl')
			->will($this->returnValue('the://url/'));
		$config->setData($configData);
		$model->setConfig($config);

		$model->$method();
	}

	/**
	 * Force some failures to complete coverage
	 *
	 * @test
	 * @dataProvider dataProvider
	 */
	public function testSftpAuthFail($authType, $expectedMessage)
	{
		$this->setExpectedException('TrueAction_FileTransfer_Exception_Authentication', $expectedMessage);
		// Force some low-level adapter failures
		$this->replaceModel(
			'filetransfer/adapter_sftp',
			array(
				'ssh2AuthPubkeyFile' => false,
				'ssh2AuthPassword' => false
			)
		);
		$model = Mage::getModel('filetransfer/protocol_types_sftp');
		$configData = $model->getConfig()
			->setAuthType($authType)
			->getData();
		$config = $this->getModelMock('filetransfer/protocol_types_sftp_config', array('getUrl'));
		$config->expects($this->any())
			->method('getUrl')
			->will($this->returnValue('the://url/'));
		$config->setData($configData);
		$model->setConfig($config);

		$model->login();
	}

	/**
	 * Force some failures to complete coverage
	 *
	 * @test
	 * @dataProvider dataProvider
	 */
	public function testSftpTransferFail($method, $fopen, $fwrite, $message)
	{
		$this->setExpectedException('TrueAction_FileTransfer_Exception_Transfer', $message);
		// Force some low-level adapter failures
		$this->replaceModel(
			'filetransfer/adapter_sftp',
			array (
				'fopen' => $fopen,
				'fwrite' => $fwrite,
				'streamGetContents' => self::FILE1_CONTENTS,
				'fclose' => true,
			)
		);
		$a = $b = 'foo';

		$model = Mage::getModel('filetransfer/protocol_types_sftp');

		$config = $this->getModelMock('filetransfer/protocol_types_sftp_config', array('getUrl'));
		$configData = $model->getConfig()
			->getData();
		$config->expects($this->any())
			->method('getUrl')
			->will($this->returnValue('the://url/'));
		$config->setData($configData);
		$model->setConfig($config);

		$model->$method($a, $b);
	}
}
