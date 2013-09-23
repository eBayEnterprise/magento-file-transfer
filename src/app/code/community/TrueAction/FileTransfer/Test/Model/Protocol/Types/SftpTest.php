<?php
/**
 * Unit Tests for Protocol/ Type/ Sftp
 *
 */
class TrueAction_FileTransfer_Test_Model_Protocol_Types_SftpTest extends TrueAction_FileTransfer_Test_Abstract
{
	const TESTBASE_DIR_NAME = 'testBase';
	const DIR1_NAME         = 'there';
	const FILE1_NAME        = 'munsters.txt';
	const FILE1_CONTENTS    = 'The Munsters is an American television sitcom depicting the home life of a family of benign monsters.';
	const DIR2_NAME         = 'here';
	const FILE2_NAME        = 'addams.txt';
	const FILE3_NAME        = 'munsters.xml';

	private $_vfs;

	public function setUp()
	{
		$this->_vfs = $this->getFixture()->getVfs();
		$this->_vfs->apply(
			array(
				self::TESTBASE_DIR_NAME =>
				array(
					self::DIR1_NAME =>
					array(
						self::FILE1_NAME => self::FILE1_CONTENTS,
						self::FILE3_NAME => '',
					),
					self::DIR2_NAME =>
					array(
						self::FILE2_NAME => '',
					),
				)
			)
		);
		$this->_vRemoteDir  = $this->_vfs->url(self::TESTBASE_DIR_NAME . '/' . self::DIR1_NAME);
		$this->_vRemoteFile = $this->_vfs->url(self::TESTBASE_DIR_NAME . '/' . self::DIR1_NAME . '/' . self::FILE1_NAME);
		$this->_vLocalDir   = $this->_vfs->url(self::TESTBASE_DIR_NAME . '/' . self::DIR2_NAME);
		$this->_vLocalFile  = $this->_vfs->url(self::TESTBASE_DIR_NAME . '/' . self::DIR2_NAME . '/' . self::FILE2_NAME);
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
			array(
				'fclose'             => $this->returnValue(true),
				'fread'              => $this->returnValue(self::FILE1_CONTENTS),
				'fopen'              => $this->returnValue(fopen($this->_vRemoteFile, 'wb+')),
				'fwrite'             => $this->returnValue(100),
				'streamGetContents'  => $this->returnValue(self::FILE1_CONTENTS),
				'ssh2Connect'        => $this->returnValue(true),
				'ssh2Sftp'           => $this->returnValue(true),
				'ssh2AuthPubkeyFile' => $this->returnValue(true),
				'ssh2AuthPassword'   => $this->returnValue(true),
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
			array(
				'fopen' => $this->returnValue(false),
			)
		);
		$methods = array('connect', 'login', 'initSftp');
		$model = $this->getModelMock('filetransfer/protocol_types_sftp', $methods);
		foreach ($methods as $mockedMethod) {
			$model->expects($this->any())
				->method($mockedMethod)
				->will($this->returnSelf());
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
		$this->setExpectedException('TrueAction_FileTransfer_Exception_Transfer', 'the://url/ transfer error: Failed to write /vfs:/testBase/there/munsters.txt to the local system');
		// Simulate the low-level Adapter that fails on fwrite
		$config = $this->getModelMock('filetransfer/protocol_types_sftp_config', array('getUrl'));
		$config->expects($this->any())
			->method('getUrl')
			->will($this->returnValue('the://url/'));
		$this->replaceModel(
			'filetransfer/adapter_sftp',
			array(
				'fclose'             => $this->returnValue(true),
				'fread'              => $this->returnValue(self::FILE1_CONTENTS),
				'fopen'              => $this->returnValue(fopen($this->_vLocalFile, 'wb+')),
				'fwrite'             => $this->returnValue(false),
				'streamGetContents'  => $this->returnValue(self::FILE1_CONTENTS),
				'ssh2Connect'        => $this->returnValue(true),
				'ssh2Sftp'           => $this->returnValue(true),
				'ssh2AuthPubkeyFile' => $this->returnValue(true),
				'ssh2AuthPassword'   => $this->returnValue(true),
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
		$methods = array();
		foreach ($mockedMethods as $mockMethod => $retVal) {
			$methods[$mockMethod] = $this->returnValue($retVal);
		}
		$this->replaceModel('filetransfer/adapter_sftp', $methods);

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
				'ssh2AuthPubkeyFile' => $this->returnValue(false),
				'ssh2AuthPassword'   => $this->returnValue(false),
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
			array(
				'fopen'             => $this->returnValue($fopen),
				'fwrite'            => $this->returnValue($fwrite),
				'streamGetContents' => $this->returnValue(self::FILE1_CONTENTS),
				'fclose'            => $this->returnValue(true),
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

	/**
	 * Test getting all files from a directory that match a given pattern
	 *
	 * @test
	 */
	public function testGetAllFiles()
	{
		$localStream = fopen($this->_vLocalFile, 'w+');
		$remoteStream = fopen($this->_vRemoteFile, 'r');
		$sftpResource = 'sftp resource';
		$sshRemotePath = "ssh2.sftp://{$sftpResource}/vfs:/" . self::TESTBASE_DIR_NAME . '/' . self::DIR1_NAME;

		$adapter = $this->getModelMock('filetransfer/adapter_sftp', array(
			'ssh2Connect', 'ssh2Sftp', 'ssh2AuthPassword', 'opendir', 'closedir',
			'readdir', 'isFile', 'fopen', 'fclose', 'fwrite', 'streamGetContents'
		));
		$adapter->expects($this->any())
			->method('ssh2Connect')
			->will($this->returnValue(true));
		$adapter->expects($this->any())
			->method('ssh2Sftp')
			->will($this->returnValue($sftpResource));
		$adapter->expects($this->any())
			->method('ssh2AuthPassword')
			->will($this->returnValue(true));

		$adapter->expects($this->any())
			->method('opendir')
			->with($this->identicalTo($sshRemotePath))
			->will($this->returnValue($this->_vRemoteDir));
		$adapter->expects($this->any())
			->method('closedir')
			->with($this->identicalTo($this->_vRemoteDir))
			->will($this->returnValue(true));
		$adapter->expects($this->exactly(3))
			->method('readdir')
			->with($this->identicalTo($this->_vRemoteDir))
			->will($this->onConsecutiveCalls(self::FILE1_NAME, self::FILE3_NAME, false));
		$adapter->expects($this->once())
			->method('isFile')
			->with(
				$this->logicalOr(
					$this->identicalTo($sshRemotePath . '/' . self::FILE1_NAME),
					$this->identicalTo($sshRemotePath . '/' . self::FILE3_NAME)
				)
			)
			->will($this->returnValue($this->returnValue(true)));
		$adapter->expects($this->exactly(2))
			->method('fopen')
			->with(
				$this->logicalOr(
					$this->identicalTo($sshRemotePath . '/' . self::FILE1_NAME),
					$this->identicalTo('vfs:/' . self::TESTBASE_DIR_NAME . '/' . self::DIR2_NAME . '/' . self::FILE1_NAME)
				),
				$this->logicalOr($this->identicalTo('r'), $this->identicalTo('w+'))
			)
			->will($this->onConsecutiveCalls($localStream, $remoteStream));
		$adapter->expects($this->exactly(2))
			->method('fclose')
			->with(
				$this->logicalOr($this->identicalTo($remoteStream), $this->identicalTo($localStream))
			)
			->will($this->returnValue(true));
		$adapter->expects($this->any())
			->method('fwrite')
			->with($this->identicalTo($localStream), $this->identicalTo(self::FILE1_CONTENTS))
			->will($this->returnValue(123));
		$adapter->expects($this->once())
			->method('streamGetContents')
			->with($this->identicalTo($remoteStream))
			->will($this->returnValue(self::FILE1_CONTENTS));

		$model = Mage::getModel('filetransfer/protocol_types_sftp', array('adapter' => $adapter));

		$this->assertTrue($model->getAllFiles($this->_vLocalDir, $this->_vRemoteDir, '*.txt'));

		fclose($localStream);
		fclose($remoteStream);
	}

	/**
	 * Attempting to retrieve zero files should not cause errors.
	 *
	 * @test
	 */
	public function testGetAllFilesNoMatching()
	{
		$localStream = fopen($this->_vLocalFile, 'w+');
		$remoteStream = fopen($this->_vRemoteFile, 'r');
		$sftpResource = 'sftp resource';
		$sshRemotePath = "ssh2.sftp://{$sftpResource}/vfs:/" . self::TESTBASE_DIR_NAME . '/' . self::DIR1_NAME;

		$adapter = $this->getModelMock('filetransfer/adapter_sftp', array(
			'ssh2Connect', 'ssh2Sftp', 'ssh2AuthPassword', 'opendir', 'closedir',
			'readdir', 'isFile', 'fopen', 'fclose', 'fwrite', 'streamGetContents'
		));
		$adapter->expects($this->any())
			->method('ssh2Connect')
			->will($this->returnValue(true));
		$adapter->expects($this->any())
			->method('ssh2Sftp')
			->will($this->returnValue($sftpResource));
		$adapter->expects($this->any())
			->method('ssh2AuthPassword')
			->will($this->returnValue(true));

		$adapter->expects($this->any())
			->method('opendir')
			->with($this->identicalTo($sshRemotePath))
			->will($this->returnValue($this->_vRemoteDir));
		$adapter->expects($this->any())
			->method('closedir')
			->with($this->identicalTo($this->_vRemoteDir))
			->will($this->returnValue(true));
		$adapter->expects($this->once())
			->method('readdir')
			->with($this->identicalTo($this->_vRemoteDir))
			->will($this->returnValue(false));
		$adapter->expects($this->never())
			->method('isFile');
		$adapter->expects($this->never())
			->method('fopen');
		$adapter->expects($this->never())
			->method('fclose');
		$adapter->expects($this->never())
			->method('fwrite');
		$adapter->expects($this->never())
			->method('streamGetContents');

		$this->replaceByMock('model', 'filetransfer/adapter_sftp', $adapter);

		$model = Mage::getModel('filetransfer/protocol_types_sftp');

		$this->assertTrue($model->getAllFiles($this->_vLocalDir, $this->_vRemoteDir, 'nothing'));

		fclose($localStream);
		fclose($remoteStream);
	}

	/**
	 * Ensure that a SFTP connection cannot be made and authed, that no remote
	 * directory access is attempted.
	 *
	 * @test
	 * @dataProvider dataProvider
	 */
	public function testDoNotAttemptRemoteDirAccessIfNoAuth($connect, $sftp, $auth)
	{
		$adapter = $this->getModelMock('filetransfer/adapter_sftp', array(
			'opendir'
		));
		$adapter->expects($this->never())
			->method('opendir');
		$this->replaceByMock('model', 'filetransfer/adapter_sftp', $adapter);

		$model = $this->getModelMock('filetransfer/protocol_types_sftp', array(
			'connect', 'login', 'initSftp'
		));
		$model->expects($this->any())
			->method('connect')
			->will($connect ? $this->returnSelf() : $this->throwException(new TrueAction_FileTransfer_Exception_Connection()));
		$model->expects($this->any())
			->method('login')
			->will($sftp ? $this->returnSelf() : $this->throwException(new TrueAction_FileTransfer_Exception_Connection()));
		$model->expects($this->any())
			->method('initSftp')
			->will($auth ? $this->returnSelf() : $this->throwException(new TrueAction_FileTransfer_Exception_Authentication()));

		// set up expected exceptions - if no connect or sftp, should have connection exception
		// if no auth, should have auth exception.
		if (!($connect && $sftp)) {
			$this->setExpectedException('TrueAction_FileTransfer_Exception_Connection');
		} elseif (!$auth) {
			$this->setExpectedException('TrueAction_FileTransfer_Exception_Authentication');
		}
		$model->getAllFiles($this->_vLocalDir, $this->_vRemoteDir, '*.txt');
	}

	/**
	 * Test unlinking a file via the ssh2.sftp protocol. Primary concerns are to:
	 * 1. Ensure the connection is created, authed and the sftp subsytem is created
	 * 2. The adapter's unlink method is called with a proper ssh2.sftp wrapped path.
	 *
	 * @test
	 */
	public function testUnlinkRemoteFile()
	{
		// clearly not a real sftp resource but will at least be testable in the ssh2.sftp path
		$sftpResource = 'sftp resource';
		$remotePath = self::TESTBASE_DIR_NAME . '/' . self::DIR1_NAME . '/' . self::FILE1_NAME;

		$adapter = $this->getModelMock('filetransfer/adapter_sftp', array(
			'ssh2Connect', 'ssh2Sftp', 'ssh2AuthPassword', 'unlink',
		));
		$adapter->expects($this->once())
			->method('ssh2Connect')
			->will($this->returnValue(true));
		$adapter->expects($this->once())
			->method('ssh2Sftp')
			->will($this->returnValue($sftpResource));
		$adapter->expects($this->once())
			->method('ssh2AuthPassword')
			->will($this->returnValue(true));
		$adapter->expects($this->once())
			->method('unlink')
			// remote path gets "normal"ed - leading slash and any duplicate slashes removed
			->with($this->identicalTo("ssh2.sftp://{$sftpResource}/{$remotePath}"))
			->will($this->returnValue(true));

		$sftp = Mage::getModel('filetransfer/protocol_types_sftp', array('adapter' => $adapter));
		$this->assertTrue($sftp->deleteFile($remotePath));
	}

	/**
	 * Ensure that a SFTP connection cannot be made and authed, that no remote
	 * directory access is attempted.
	 *
	 * @test
	 * @dataProvider dataProvider
	 */
	public function testDoNotAttemptUnlinkIfNoAuth($connect, $sftp, $auth)
	{
		$adapter = $this->getModelMock('filetransfer/adapter_sftp', array(
			'unlink'
		));
		$adapter->expects($this->never())
			->method('unlink');
		$this->replaceByMock('model', 'filetransfer/adapter_sftp', $adapter);

		$model = $this->getModelMock('filetransfer/protocol_types_sftp', array(
			'connect', 'login', 'initSftp'
		));
		$model->expects($this->any())
			->method('connect')
			->will($connect ? $this->returnSelf() : $this->throwException(new TrueAction_FileTransfer_Exception_Connection()));
		$model->expects($this->any())
			->method('login')
			->will($sftp ? $this->returnSelf() : $this->throwException(new TrueAction_FileTransfer_Exception_Connection()));
		$model->expects($this->any())
			->method('initSftp')
			->will($auth ? $this->returnSelf() : $this->throwException(new TrueAction_FileTransfer_Exception_Authentication()));

		// set up expected exceptions - if no connect or sftp, should have connection exception
		// if no auth, should have auth exception.
		if (!($connect && $sftp)) {
			$this->setExpectedException('TrueAction_FileTransfer_Exception_Connection');
		} elseif (!$auth) {
			$this->setExpectedException('TrueAction_FileTransfer_Exception_Authentication');
		}

		$this->assertFalse($model->deleteFile($this->_vRemoteDir));
	}

}
