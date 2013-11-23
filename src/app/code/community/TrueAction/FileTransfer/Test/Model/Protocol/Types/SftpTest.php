<?php
class TrueAction_FileTransfer_Test_Model_Protocol_Types_SftpTest extends TrueAction_FileTransfer_Test_Abstract
{
	const DIR1_NAME = 'there';
	const DIR2_NAME = 'here';
	const FILE1_CONTENTS = 'The Munsters is an American television sitcom depicting the home life of a family of benign monsters.';
	const FILE1_NAME = 'munsters.txt';
	const FILE2_NAME = 'addams.txt';
	const FILE3_NAME = 'munsters.xml';
	const TESTBASE_DIR_NAME = 'testBase';
	/**
	 * Test _construct method to set up a Net_SFTP instance and log it in
	 * @mock TrueAction_FileTransfer_Model_Protocol_Types_Sftp::_initCon make sure a Net_SFTP object is set up
	 * @mock TrueAction_FileTransfer_Model_Protocol_Types_Sftp::_login make sure the connection is logged in
	 * @mock TrueAction_FileTransfer_Model_Protocol_Types_Sftp::getConfigModel
	 * @mock TrueAction_FileTransfer_Model_Protocol_Types_Sftp::setConfigModel ensure a SFTP config model is set up
	 * @test
	 */
	public function testConstruct()
	{
		$sftpConfig = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp_config')
			->disableOriginalConstructor()
			->getMock();
		$this->replaceByMock('model', 'filetransfer/protocol_types_sftp_config', $sftpConfig);

		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->disableOriginalConstructor()
			->setMethods(array('_initCon', '_login', 'getConfigModel', 'setConfigModel'))
			->getMock();
		$sftp
			->expects($this->once())
			->method('_initCon')
			->will($this->returnSelf());
		$sftp
			->expects($this->once())
			->method('_login')
			->will($this->returnSelf());
		$sftp
			->expects($this->any())
			->method('getConfig')
			->will($this->returnValue(array(
				'store' => 0, 'config_path' => 'config/path', 'protocol_code' => 'sftp'
			)));
		$sftp
			->expects($this->once())
			->method('setConfigModel')
			->with($this->identicalTo($sftpConfig))
			->will($this->returnSelf());

		$sftpRef = new ReflectionObject($sftp);
		$construct = $sftpRef->getMethod('_construct');
		$construct->setAccessible(true);
		$construct->invoke($sftp);
	}
	/**
	 * Test uploading a file to a remote server.
	 * @mock Net_SFTP::put to check args
	 * @mock Net_SFTP::disconnect called by __desctruct to clean up connections, stub so it doesn't error out
	 * @stub TrueAction_FileTransfer_Model_Protocol_Types_Sftp::getCon swap out the mock SFTP instance
	 * @mock TrueAction_FileTransfer_Model_Protocol_Types_Sftp::_getRemotePath to check args
	 * @stub TrueAction_FileTransfer_Model_Protocol_Types_Sftp::_logPut
	 * @test
	 */
	public function testSendFile()
	{
		$loc = 'local';
		$rem = 'remote';
		$netSftp = $this
			->getMockBuilder('Net_SFTP')
			->setMethods(array('put', 'disconnect'))
			->disableOriginalConstructor()
			->getMock();
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getCon', '_getRemotePath', '_logPut'))
			->disableOriginalConstructor()
			->getMock();
		$netSftp
			->expects($this->once())
			->method('put')
			->with($this->equalTo('/' . $rem), $this->equalTo($loc), $this->equalTo(NET_SFTP_LOCAL_FILE))
			->will($this->returnValue(true));
		// Assume getCon returns a Net_SFTP object carrying an authenticated, active connection.
		$sftp
			->expects($this->any())
			->method('getCon')
			->will($this->returnValue($netSftp));
		// Assume _getRemotePath prepends a slash to the remote file name.
		$sftp
			->expects($this->once())
			->method('_getRemotePath')
			->with($this->equalTo($rem))
			->will($this->returnValue('/' . $rem));
		// Assert that sendFile mirrors the return value of put.
		$this->assertSame(true, $sftp->sendFile($loc, $rem));
	}
	/**
	 * Test downloading a file from the remote server.
	 * @mock Net_SFTP::get to check args.
	 * @mock Net_SFTP::disconnect called by __desctruct to clean up connections
	 * @stub TrueAction_FileTransfer_Model_Protocol_Types_Sftp::getCon
	 * @mock TrueAction_FileTransfer_Model_Protocol_Types_Sftp::_getRemotePath to check args
	 * @test
	 */
	public function testGetFile()
	{
		$loc = 'local';
		$rem = 'remote';
		$netSftp = $this
			->getMockBuilder('Net_SFTP')
			->setMethods(array('get', 'disconnect'))
			->disableOriginalConstructor()
			->getMock();
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getCon', '_getRemotePath', '_logGet'))
			->disableOriginalConstructor()
			->getMock();
		$netSftp
			->expects($this->once())
			->method('get')
			->with($this->equalTo('/' . $rem), $this->equalTo($loc))
			->will($this->returnValue(true));
		// Assume getCon returns a Net_SFTP object carrying an authenticated, active connection.
		$sftp
			->expects($this->any())
			->method('getCon')
			->will($this->returnValue($netSftp));
		// Assume _getRemotePath prepends a slash to the remote file name.
		$sftp
			->expects($this->once())
			->method('_getRemotePath')
			->with($this->equalTo($rem))
			->will($this->returnValue('/' . $rem));
		// Assert that getFile mirrors the return value of Net_SFTP::get
		$this->assertTrue($sftp->getFile($loc, $rem));
	}
	/**
	 * Test deleting a file from the remote server.
	 * @mock Net_SFTP::delete to check args.
	 * @mock Net_SFTP::disconnect called by __desctruct to clean up connections
	 * @stub TrueAction_FileTransfer_Model_Protocol_Types_Sftp::getCon
	 * @mock TrueAction_FileTransfer_Model_Protocol_Types_Sftp::_getRemotePath to check args
	 * @test
	 */
	public function testDeleteFile()
	{
		$rem = 'remote';
		$netSftp = $this
			->getMockBuilder('Net_SFTP')
			->setMethods(array('delete', 'disconnect'))
			->disableOriginalConstructor()
			->getMock();
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getCon', '_getRemotePath', '_logDel'))
			->disableOriginalConstructor()
			->getMock();
		$netSftp
			->expects($this->once())
			->method('delete')
			->with($this->equalTo('/' . $rem))
			->will($this->returnValue(true));
		// Assume getCon returns a Net_SFTP object carrying an authenticated, active connection.
		$sftp
			->expects($this->any())
			->method('getCon')
			->will($this->returnValue($netSftp));
		// Assume _getRemotePath prepends a slash to the remote file name.
		$sftp
			->expects($this->once())
			->method('_getRemotePath')
			->with($this->equalTo($rem))
			->will($this->returnValue('/' . $rem));
		// Assert that getFile mirrors the return value of Net_SFTP::get
		$this->assertTrue($sftp->deleteFile($rem));
	}
	/**
	 * Test listing the files in a remote directory matching a pattern.
	 * @mock Net_SFTP::chdir to check that it's called.
	 * @mock Net_SFTP::rawlist to check that it's called.
	 * @mock Net_SFTP::disconnect called by __desctruct to clean up connections
	 * @stub TrueAction_FileTransfer_Model_Protocol_Types_Sftp::getCon
	 * @mock TrueAction_FileTransfer_Model_Protocol_Types_Sftp::_getRemotePath to check args
	 * @test
	 * @dataProvider dataProvider
	 */
	public function testListFilesMatchingPat($remote, $rawlistResults, $matchingResults)
	{
		$pat = 'f*';
		// Test that we get a list of names on the remote server.
		$netSftp = $this
			->getMockBuilder('Net_SFTP')
			->setMethods(array('rawlist', 'delete', 'disconnect'))
			->disableOriginalConstructor()
			->getMock();
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getCon', '_getRemotePath'))
			->disableOriginalConstructor()
			->getMock();
		$netSftp
			->expects($this->once())
			->method('rawlist')
			->with($this->identicalTo('/' . $remote))
			->will($this->returnValue($rawlistResults));
		// Assume getCon returns a Net_SFTP object carrying an authenticated, active connection.
		$sftp
			->expects($this->once())
			->method('getCon')
			->will($this->returnValue($netSftp));
		// Assume _getRemotePath prepends a slash to the remote directory.
		$sftp
			->expects($this->once())
			->method('_getRemotePath')
			->with($this->equalTo($remote))
			->will($this->returnValue('/' . $remote));
		// We only want regular files starting with 'f' (see $pat)
		// so we expect to see only 'foo' and 'fred'.
		$this->assertSame($matchingResults, $sftp->listFilesMatchingPat($remote, $pat));
	}
	/**
	 * Test fetching all the files matching a pattern.
	 * @mock TrueAction_FileTransfer_Model_Protocol_Types_Sftp::listFilesMatchingPat to check it's called
	 * @mock TrueAction_FileTransfer_Model_Protocol_Types_Sftp::getFile to check args
	 * @test
	 */
	public function testGetAllFiles()
	{
		$loc = 'local';
		$rem = 'remote';
		$pat = 'f*';
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getFile', 'listFilesMatchingPat'))
			->disableOriginalConstructor()
			->getMock();
		// Assume listFilesMatchingPat will return two files.
		$sftp
			->expects($this->once())
			->method('listFilesMatchingPat')
			->with($this->equalTo($rem), $this->equalTo($pat))
			->will($this->returnValue(array("/$rem/foo", "/$rem/fred")));
		$sftp
			->expects($this->at(1))
			->method('getFile')
			->with($this->identicalTo("local/foo"), $this->identicalTo("/$rem/foo"))
			->will($this->returnValue(true));
		$sftp
			->expects($this->at(2))
			->method('getFile')
			->with($this->identicalTo("local/fred"), $this->identicalTo("/$rem/fred"))
			->will($this->returnValue(true));
		// Test that the return value mirrors the anded return values of getFile.
		$this->assertTrue($sftp->getAllFiles($loc, $rem, $pat));
	}
	/**
	 * When failing to get a list of files on the remote, getAllFiles should exit with false
	 * @mock TrueAction_FileTransfer_Model_Protocol_Types_Sftp::listFilesMatchingPat stub list of files on the remote, considered to have failed in this test
	 * @mock TrueAction_FileTransfer_Model_Protocol_Types_Sftp::getFile to check args
	 * @test
	 */
	public function testGetAllFilesFailure()
	{
		$loc = 'local';
		$rem = 'remote';
		$pat = 'f*';
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getFile', 'listFilesMatchingPat'))
			->disableOriginalConstructor()
			->getMock();
		// Assume listFilesMatchingPat will return two files.
		$sftp
			->expects($this->once())
			->method('listFilesMatchingPat')
			->with($this->equalTo($rem), $this->equalTo($pat))
			->will($this->returnValue(false));
		$sftp
			->expects($this->never())
			->method('getFile');
		// Test that the return value mirrors the anded return values of getFile.
		$this->assertFalse($sftp->getAllFiles($loc, $rem, $pat));
	}
	/**
	 * Test writing a string to a remote file.
	 * @stub TrueAction_FileTransfer_Model_Protocol_Types_Sftp::getCon
	 * @mock TrueAction_FileTransfer_Model_Protocol_Types_Sftp::_getRemotePath to check args
	 * @mock Net_SFTP::put to check arguments
	 * @mock Net_SFTP::disconnect called by __desctruct to clean up connections
	 * @test
	 */
	public function testSendString()
	{
		$str = 'foo';
		$rem = 'remote';
		$netSftp = $this
			->getMockBuilder('Net_SFTP')
			->setMethods(array('put', 'delete', 'disconnect'))
			->disableOriginalConstructor()
			->getMock();
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getCon', '_getRemotePath', '_logPut'))
			->disableOriginalConstructor()
			->getMock();
		$netSftp
			->expects($this->once())
			->method('put')
			->with($this->identicalTo('/' . $rem), $this->identicalTo($str))
			->will($this->returnValue(true));
		$sftp
			->expects($this->once())
			->method('getCon')
			->will($this->returnValue($netSftp));
		$sftp
			->expects($this->once())
			->method('_getRemotePath')
			->with($this->identicalTo($rem))
			->will($this->returnValue('/' . $rem));
		// Test that the return value mirrors the return value of put.
		$this->assertSame(true, $sftp->sendString($str, $rem));
	}
	/**
	 * Test reading a remote file into a string.
	 * @stub TrueAction_FileTransfer_Model_Protocol_Types_Sftp::getCon
	 * @mock TrueAction_FileTransfer_Model_Protocol_Types_Sftp::_getRemotePath to check args
	 * @mock Net_SFTP::get to check arguments
	 * @mock Net_SFTP::disconnect called by __desctruct to clean up connections
	 * @test
	 */
	public function testGetString()
	{
		$str = 'bar';
		$rem = 'remote';
		$netSftp = $this
			->getMockBuilder('Net_SFTP')
			->setMethods(array('get', 'delete', 'disconnect'))
			->disableOriginalConstructor()
			->getMock();

		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getCon', '_getRemotePath', '_logGet'))
			->disableOriginalConstructor()
			->getMock();
		$netSftp
			->expects($this->once())
			->method('get')
			->with($this->identicalTo('/' . $rem))
			->will($this->returnValue($str));
		$sftp
			->expects($this->once())
			->method('getCon')
			->will($this->returnValue($netSftp));
		$sftp
			->expects($this->once())
			->method('_getRemotePath')
			->with($this->identicalTo($rem))
			->will($this->returnValue('/' . $rem));
		// Test that the return value of getString mirrors the return value of Net_SFTP::get
		$this->assertSame($str, $sftp->getString($rem));
	}
	/**
	 * Test passing the buck to the more specific _loginKey and _loginPass functions
	 * @stub TrueAction_FileTransfer_Protocol_Types_Sftp_Config::getAuthType to test branching
	 * @stub TrueAction_FileTransfer_Protocol_Types_Sftp::getConfigModel
	 * @mock TrueAction_FileTransfer_Protocol_Types_Sftp::_loginKey to check that it's called
	 * @mock TrueAction_FileTransfer_Protocol_Types_Sftp::_loginPass to check that it's called
	 * @test
	 */
	public function testLogin()
	{
		$authTypes = array('pub_key', 'pass'); // If there were more than two it might be worth a provider.
		$cfg = $this->getModelMock('filetransfer/protocol_types_sftp_config', array('getAuthType'));
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getConfigModel', '_loginKey', '_loginPass'))
			->disableOriginalConstructor()
			->getMock();
		$cfg
			->expects($this->exactly(2))
			->method('getAuthType')
			->will($this->onConsecutiveCalls($this->returnValue($authTypes[0]), $this->returnValue($authTypes[1])));
		$sftp
			->expects($this->exactly(2))
			->method('getConfigModel')
			->will($this->returnValue($cfg));
		$sftp
			->expects($this->once())
			->method('_loginKey')
			->will($this->returnSelf());
		$sftp
			->expects($this->once())
			->method('_loginPass')
			->will($this->returnSelf());

		$sftpRef = new ReflectionObject($sftp);
		$loginMethod = $sftpRef->getMethod('_login');
		$loginMethod->setAccessible(true);
		// first pass will auth via key
		$this->assertSame($sftp, $loginMethod->invoke($sftp));
		// second pass will auth via pass
		$this->assertSame($sftp, $loginMethod->invoke($sftp));
	}
	public function testLoginPass()
	{
		$user = 'fred';
		$pass = 'derf';
		$cfg = $this
			->getModelMock('filetransfer/protocol_types_sftp_config', array('getUsername', 'getPassword'));
		$netSftp = $this
			->getMockBuilder('Net_SFTP')
			->setMethods(array('login', 'delete', 'disconnect'))
			->disableOriginalConstructor()
			->getMock();
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getCon', 'getConfigModel'))
			->disableOriginalConstructor()
			->getMock();
		$cfg
			->expects($this->once())
			->method('getUsername')
			->will($this->returnValue($user));
		$cfg
			->expects($this->once())
			->method('getPassword')
			->will($this->returnValue($pass));
		$netSftp
			->expects($this->once())
			->method('login')
			->with($this->identicalTo($user), $this->identicalTo($pass))
			->will($this->returnValue(true));
		$sftp
			->expects($this->once())
			->method('getCon')
			->will($this->returnValue($netSftp));
		$sftp
			->expects($this->once())
			->method('getConfigModel')
			->will($this->returnValue($cfg));
		$sftpRef = new ReflectionObject($sftp);
		$loginPass = $sftpRef->getMethod('_loginPass');
		$loginPass->setAccessible(true);
		$this->assertSame($sftp, $loginPass->invoke($sftp));
	}
	public function testLoginPassFailure()
	{
		$user = 'fred';
		$pass = 'derf';
		$cfg = $this
			->getModelMock('filetransfer/protocol_types_sftp_config', array('getUsername', 'getPassword'));
		$cfg
			->expects($this->any())
			->method('getUsername')
			->will($this->returnValue($user));
		$cfg
			->expects($this->any())
			->method('getPassword')
			->will($this->returnValue($pass));
		$netSftp = $this
			->getMockBuilder('Net_SFTP')
			->disableOriginalConstructor()
			->setMethods(array('login', 'disconnect'))
			->getMock();
		$netSftp
			->expects($this->once())
			->method('login')
			->with($this->identicalTo($user), $this->identicalTo($pass))
			->will($this->returnValue(false));
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->disableOriginalConstructor()
			->setMethods(array('getCon', 'getConfigModel'))
			->getMock();
		$sftp
			->expects($this->any())
			->method('getCon')
			->will($this->returnValue($netSftp));
		$sftp
			->expects($this->any())
			->method('getConfigModel')
			->will($this->returnValue($cfg));

		$sftpRef = new ReflectionObject($sftp);
		$loginMethod = $sftpRef->getMethod('_loginPass');
		$loginMethod->setAccessible(true);

		$this->setExpectedException('TrueAction_FileTransfer_Exception_Authentication');
		$loginMethod->invoke($sftp);
	}
	/**
	 * Test that getPrivateKey loads the key string from config and
	 * returns it loaded into a Crypt_RSA container object.
	 * @mock Crypt_RSA::loadKey to check args, make sure correct key is loaded
	 * @mock TrueAction_FileTransfer_Protocol_Types_Sftp_Config::getPrivateKey stub config value
	 * @stub TrueAction_FileTransfer_Protocol_Types_Sftp::getConfigModel get the stubbed config
	 * @stub TrueAction_FileTransfer_Protocol_Types_Sftp::getRsa get the mocked Crypt_RSA object
	 * @test
	 */
	public function testGetPrivateKey()
	{
		$key = 'g minor';
		$rsa = $this->getMock('Crypt_RSA', array('loadKey'));
		$cfg = $this->getModelMock('filetransfer/protocol_types_sftp_config', array('getPrivateKey'));
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getRsa', 'getConfigModel'))
			->disableOriginalConstructor()
			->getMock();
		$rsa
			->expects($this->once())
			->method('loadKey')
			->with($this->identicalTo($key));
		$cfg
			->expects($this->any())
			->method('getPrivateKey')
			->will($this->returnValue($key));
		$sftp
			->expects($this->any())
			->method('getConfigModel')
			->will($this->returnValue($cfg));
		$sftp
			->expects($this->any())
			->method('getRsa')
			->will($this->returnValue($rsa));

		$sftpRef = new ReflectionObject($sftp);
		$getPrivKeyMethod = $sftpRef->getMethod('_getPrivateKey');
		$getPrivKeyMethod->setAccessible(true);

		$this->assertSame($rsa, $getPrivKeyMethod->invoke($sftp));
	}
	/**
	 * Test logging in the Net SFTP object using a private key.
	 * @mock TrueAction_FileTransfer_Model_Protocol_Types_Sftp_Config::getUsername stub the config value
	 * @mock Crypt_RSA stub instance used as the private key used to login with
	 * @mock Net_SFTP::login make sure the instance is logged in with the proper username and key
	 * @mock Net_SFTP::disconnect called by __destruct, just make sure it doesn't error out
	 * @mock TryeAction_FileTransfer_Model_Protocol_Types_Sftp::getCon swap out the mock Net_SFTP instance
	 * @mock TryeAction_FileTransfer_Model_Protocol_Types_Sftp::getConfigModel swap out the mock config model
	 * @mock TryeAction_FileTransfer_Model_Protocol_Types_Sftp::_getPrivateKey swap out the mock RSA instance
	 */
	public function testLoginKey()
	{
		$user = 'fred';
		$rsa = $this
			->getMockBuilder('Crypt_RSA')
			->disableOriginalConstructor()
			->getMock();
		$cfg = $this
			->getModelMock('filetransfer/protocol_types_sftp_config', array('getUsername'));
		$netSftp = $this
			->getMockBuilder('Net_SFTP')
			->setMethods(array('login', 'disconnect'))
			->disableOriginalConstructor()
			->getMock();
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getCon', 'getConfigModel', '_getPrivateKey'))
			->disableOriginalConstructor()
			->getMock();
		$cfg
			->expects($this->once())
			->method('getUsername')
			->will($this->returnValue($user));
		$netSftp
			->expects($this->once())
			->method('login')
			->with($this->identicalTo($user), $this->identicalTo($rsa))
			->will($this->returnValue(true));
		$sftp
			->expects($this->once())
			->method('getCon')
			->will($this->returnValue($netSftp));
		$sftp
			->expects($this->once())
			->method('_getPrivateKey')
			->will($this->returnValue($rsa));
		$sftp
			->expects($this->once())
			->method('getConfigModel')
			->will($this->returnValue($cfg));
		$sftpRef = new ReflectionObject($sftp);
		$loginKey = $sftpRef->getMethod('_loginKey');
		$loginKey->setAccessible(true);
		$this->assertSame($sftp, $loginKey->invoke($sftp));
	}
	/**
	 * Test logging in the Net SFTP object using a private key.
	 * @mock TrueAction_FileTransfer_Model_Protocol_Types_Sftp_Config::getUsername stub the config value
	 * @mock Crypt_RSA stub instance used as the private key used to login with
	 * @mock Net_SFTP::login make sure the instance is logged in with the proper username and key
	 * @mock Net_SFTP::disconnect called by __destruct, just make sure it doesn't error out
	 * @mock TryeAction_FileTransfer_Model_Protocol_Types_Sftp::getCon swap out the mock Net_SFTP instance
	 * @mock TryeAction_FileTransfer_Model_Protocol_Types_Sftp::getConfigModel swap out the mock config model
	 * @mock TryeAction_FileTransfer_Model_Protocol_Types_Sftp::_getPrivateKey swap out the mock RSA instance
	 */
	public function testLoginKeyFailure()
	{
		$user = 'fred';
		$rsa = $this
			->getMockBuilder('Crypt_RSA')
			->disableOriginalConstructor()
			->getMock();
		$cfg = $this
			->getModelMock('filetransfer/protocol_types_sftp_config', array('getUsername'));
		$netSftp = $this
			->getMockBuilder('Net_SFTP')
			->setMethods(array('login', 'disconnect'))
			->disableOriginalConstructor()
			->getMock();
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getCon', 'getConfigModel', '_getPrivateKey'))
			->disableOriginalConstructor()
			->getMock();
		$cfg
			->expects($this->once())
			->method('getUsername')
			->will($this->returnValue($user));
		$netSftp
			->expects($this->once())
			->method('login')
			->with($this->identicalTo($user), $this->identicalTo($rsa))
			->will($this->returnValue(false));
		$sftp
			->expects($this->once())
			->method('getCon')
			->will($this->returnValue($netSftp));
		$sftp
			->expects($this->once())
			->method('_getPrivateKey')
			->will($this->returnValue($rsa));
		$sftp
			->expects($this->once())
			->method('getConfigModel')
			->will($this->returnValue($cfg));
		$sftpRef = new ReflectionObject($sftp);
		$loginKey = $sftpRef->getMethod('_loginKey');
		$loginKey->setAccessible(true);

		$this->setExpectedException('TrueAction_FileTransfer_Exception_Authentication');
		$loginKey->invoke($sftp);
	}
	public function testGetRemotePath()
	{
		$rel = 'foo';
		$rem = '/foo/bar';
		$cfg = $this
			->getModelMock('filetransfer/protocol_types_sftp_config', array('getRemotePath'));
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getConfigModel'))
			->disableOriginalConstructor()
			->getMock();
		$cfg
			->expects($this->once())
			->method('getRemotePath') // similar method name, different class
			->will($this->returnValue($rem));
		$sftp
			->expects($this->once())
			->method('getConfigModel')
			->will($this->returnValue($cfg));
		$sftpRef = new ReflectionObject($sftp);
		$getRemotePath = $sftpRef->getMethod('_getRemotePath');
		$getRemotePath->setAccessible(true);
		$this->assertSame('/foo/bar/foo', $getRemotePath->invoke($sftp, $rel));
	}
}
