<?php
/**
 * Copyright (c) 2013-2014 eBay Enterprise, Inc.
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @copyright   Copyright (c) 2013-2014 eBay Enterprise, Inc. (http://www.ebayenterprise.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

// include this file here so the tests can make use of
// the NET_SSH2_MASK_... constants
require_once('phpseclib/Net/SSH2.php');
class EbayEnterprise_FileTransfer_Test_Model_Protocol_Types_SftpTest extends EbayEnterprise_FileTransfer_Test_Abstract
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
	 * @mock EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::_initCon make sure a Net_SFTP object is set up
	 * @mock EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::_login make sure the connection is logged in
	 * @mock EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::getConfigModel
	 * @mock EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::setConfigModel ensure a SFTP config model is set up
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
			->setMethods(array('getConfigModel', 'setConfigModel'))
			->getMock();
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
	 * Test downloading a file from the remote server.
	 * @mock Net_SFTP::get to check args.
	 * @mock Net_SFTP::disconnect called by __desctruct to clean up connections
	 * @stub EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::getCon
	 * @mock EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::_getRemotePath to check args
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
	 * Test fetching all the files matching a pattern.
	 * @mock EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::listFilesMatchingPattern to check it's called
	 * @mock EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::getFile to check args
	 */
	public function testGetAllFiles()
	{
		$loc = 'local';
		$rem = 'remote';
		$pat = 'f*';
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getFile', 'listFilesMatchingPattern'))
			->disableOriginalConstructor()
			->getMock();
		// Assume listFilesMatchingPattern will return two files.
		$sftp
			->expects($this->once())
			->method('listFilesMatchingPattern')
			->with($this->equalTo($rem), $this->equalTo($pat))
			->will($this->returnValue(array("/$rem/foo", "/$rem/fred", "/$rem/fran")));
		$sftp->expects($this->exactly(3))
			->method('getFile')
			->will($this->returnValueMap(array(
				array("$loc/foo", "/$rem/foo", true),
				array("$loc/fred", "/$rem/fred", true),
				array("$loc/fran", "/$rem/fran", false),
			)));
		// Test that the return value mirrors the anded return values of getFile.
		$this->assertSame(
			array(
				array('local' => "$loc/foo", 'remote' => "/$rem/foo"),
				array('local' => "$loc/fred", 'remote' => "/$rem/fred"),
			),
			$sftp->getAllFiles($loc, $rem, $pat)
		);
	}
	/**
	 * Test uploading a file to a remote server.
	 * @mock Net_SFTP::put to check args
	 * @mock Net_SFTP::disconnect called by __desctruct to clean up connections, stub so it doesn't error out
	 * @stub EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::getCon swap out the mock SFTP instance
	 * @mock EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::_getRemotePath to check args
	 * @stub EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::_logPut
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
	 * Test sending all files in a local directory to a remote directory.
	 * @mock EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::sendFile to check args for each file to send
	 * @mock EbayEnterprise_FileTransfer_Helper_File::listFilesInDirectory to check args and return list of local files
	 */
	public function testSendAllFiles()
	{
		$loc = 'local';
		$rem = 'remote';
		$pat = 'f*';
        $dummy_filename = 'dummy_file';

		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->disableOriginalConstructor()
			->setMethods(array('sendFile'))
			->getMock();
		$fileHelper = $this->getHelperMock(
			'filetransfer/file',
			array('listFilesInDirectory')
		);
		$fileFileInfo = $this->getMockBuilder('SPLFileInfo')
			->setConstructorArgs(array($dummy_filename))
			->setMethods(array('isFile'))
			->getMock();
		$dirFileInfo = $this->getMockBuilder('SPLFileInfo')
			->setConstructorArgs(array($dummy_filename))
			->setMethods(array('isFile'))
			->getMock();
		$this->replaceByMock('helper', 'filetransfer/file', $fileHelper);

		$fileFileInfo->expects($this->any())
			->method('isFile')
			->will($this->returnValue(true));
		$dirFileInfo->expects($this->any())
			->method('isFile')
			->will($this->returnValue(false));

		// this method returns an iterable with values of string paths to each file
		// in the directory
		$fileHelper->expects($this->once())
			->method('listFilesInDirectory')
			->with($this->identicalTo($loc), $this->identicalTo($pat))
			->will($this->returnValue(array(
				"$loc/foo" => $fileFileInfo,
				"$loc/fred" => $fileFileInfo,
				"$loc/fooDirectory" => $dirFileInfo))
		);
		$sftp->expects($this->exactly(2))
			->method('sendFile')
			->will($this->returnValueMap(array(
				array("$loc/foo", "$rem/foo", true),
				array("$loc/fred", "$rem/fred", false),
			)));

		$this->assertSame(
			array(
				array('local' => "$loc/foo", 'remote' => "$rem/foo"),
			),
			$sftp->sendAllFiles($loc, $rem, $pat)
		);
	}
	/**
	 * Test deleting a file from the remote server.
	 * @mock Net_SFTP::delete to check args.
	 * @mock Net_SFTP::disconnect called by __desctruct to clean up connections
	 * @stub EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::getCon
	 * @mock EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::_getRemotePath to check args
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
	 * @stub EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::getCon
	 * @mock EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::_getRemotePath to check args
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
		$this->assertSame($matchingResults, $sftp->listFilesMatchingPattern($remote, $pat));
	}
	/**
	 * Test writing a string to a remote file.
	 * @stub EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::getCon
	 * @mock EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::_getRemotePath to check args
	 * @mock Net_SFTP::put to check arguments
	 * @mock Net_SFTP::disconnect called by __desctruct to clean up connections
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
	 * @stub EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::getCon
	 * @mock EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::_getRemotePath to check args
	 * @mock Net_SFTP::get to check arguments
	 * @mock Net_SFTP::disconnect called by __desctruct to clean up connections
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
	public function testGetConnection()
	{
		$netSftp = $this->getMockBuilder('Net_SFTP')
			->disableOriginalConstructor()
			->setMethods(array())
			->getMock();
		$sftp = $this->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->disableOriginalConstructor()
			->setMethods(array('connect', 'login', 'getData'))
			->getMock();
		$sftp->expects($this->once())
			->method('connect')
			->will($this->returnSelf());
		$sftp->expects($this->once())
			->method('login')
			->will($this->returnSelf());
		$sftp->expects($this->once())
			->method('getData')
			->with($this->identicalTo('con'))
			->will($this->returnValue($netSftp));

		$this->assertSame($netSftp, $sftp->getCon());
	}
	/**
	 * Data Provider - returns possible NET_SSH2_MASK values and whether or not
	 * those values should be considered as having been logged in
	 * @return array argument arrays to testIsLoggedIn
	 */
	public function providerIsLoggedIn()
	{
		return array(
			array(NET_SSH2_MASK_CONSTRUCTOR, false),
			array(NET_SSH2_MASK_LOGIN, true),
			array(0, false),
		);
	}
	/**
	 * Test checking if the Net_SFTP connection has been logged in
	 * @param  int     $bitmap   value of the Net_SFTP bitmap property
	 * @param  boolean $loggedIn should the Net_SFTP instance be considered logged in
	 * @mock EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp::getData return the stub Net_SFTP instance
	 * @mock Net_SFTP
	 * @dataProvider providerIsLoggedIn
	 */
	public function testIsLoggedIn($bitmap, $loggedIn)
	{
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->disableOriginalConstructor()
			->setMethods(array('getData', 'hasCon'))
			->getMock();
		$con = $this
			->getMockBuilder('Net_SFTP')
			->disableOriginalConstructor()
			->getMock();

		$sftp
			->expects($this->any())
			->method('getData')
			->with($this->identicalTo('con'))
			->will($this->returnValue($con));
		$sftp
			->expects($this->any())
			->method('hasCon')
			->will($this->returnValue(true));
		$con->bitmap = $bitmap;
		$this->assertSame($loggedIn, $sftp->isLoggedIn());
	}
	/**
	 * If the connection has already been logged in, it should not try to login the
	 * instance again.
	 * @mock EbayEnterprise_FileTransfer_Protocol_Types_Sftp::isLoggedIn make it think the connection is logged in already
	 * @mock EbayEnterprise_FileTransfer_Protocol_Types_Sftp::_loginPass make sure it is never called
	 * @mock EbayEnterprise_FileTransfer_Protocol_Types_sftp::getConfigModel swap out a stubbed config
	 * @mock EbayEnterprise_FileTransfer_Protocol_Types_Sftp_Config::getAuthType make sure that if the auth is attempted, it should hit the password login we want to avoid
	 */
	public function testDoNotLoginAlreadyLoggedInConnection()
	{
		$cfg = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp_config')
			->disableOriginalConstructor()
			->setMethods(array('getAuthType'))
			->getMock();
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->disableOriginalConstructor()
			->setMethods(array('isLoggedIn', '_loginPass', 'getConfigModel'))
			->getMock();
		// this should never actually be called in this scenario...but I want to make sure
		// the behavior of the mocks is as true to life as possible
		$cfg
			->expects($this->any())
			->method('getAuthType')
			->will($this->returnValue('pass'));
		$sftp
			->expects($this->any())
			->method('getConfigModel')
			->will($this->returnValue($cfg));
		$sftp
			->expects($this->any())
			->method('isLoggedIn')
			->will($this->returnValue(true));
		$sftp
			->expects($this->never())
			->method('_loginPass');

		$this->assertSame($sftp, $sftp->login());
	}
	public function providerLoginAuthType()
	{
		return array(
			array('pub_key', '_loginKey'),
			array('pass', '_loginPass'),
		);
	}
	/**
	 * Test passing the buck to the more specific _loginKey and _loginPass functions
	 * @stub EbayEnterprise_FileTransfer_Protocol_Types_Sftp_Config::getAuthType to test branching
	 * @stub EbayEnterprise_FileTransfer_Protocol_Types_Sftp::getConfigModel
	 * @stub EbayEnterprise_FileTransfer_Protocol_Types_Sftp::isLoggedIn
	 * @mock EbayEnterprise_FileTransfer_Protocol_Types_Sftp::_loginKey to check that it's called
	 * @mock EbayEnterprise_FileTransfer_Protocol_Types_Sftp::_loginPass to check that it's called
	 * @dataProvider providerLoginAuthType
	 */
	public function testLogin($authType, $loginMethod)
	{
		$cfg = $this->getModelMockBuilder('filetransfer/protocol_types_sftp_config')
			->disableOriginalConstructor()
			->setMethods(array('getAuthType'))
			->getMock();
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getConfigModel', '_loginKey', '_loginPass', 'isLoggedIn'))
			->disableOriginalConstructor()
			->getMock();
		$cfg
			->expects($this->any())
			->method('getAuthType')
			->will($this->returnValue($authType));
		$sftp
			->expects($this->any())
			->method('getConfigModel')
			->will($this->returnValue($cfg));
		$sftp
			->expects($this->any())
			->method('isLoggedIn')
			->will($this->returnValue(false));
		$sftp
			->expects($this->once())
			->method($loginMethod)
			->will($this->returnSelf());

		$this->assertSame($sftp, $sftp->login());
	}
	public function testLoginPass()
	{
		$user = 'fred';
		$pass = 'derf';
		$cfg = $this->getModelMockBuilder('filetransfer/protocol_types_sftp_config')
			->disableOriginalConstructor()
			->setMethods(array('getUsername', 'getPassword'))
			->getMock();
		$netSftp = $this
			->getMockBuilder('Net_SFTP')
			->setMethods(array('login', 'delete', 'disconnect'))
			->disableOriginalConstructor()
			->getMock();
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getData', 'getConfigModel'))
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
			->method('getData')
			->with($this->identicalTo('con'))
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
	/**
	 * When a login fails, an exception should be thrown
	 */
	public function testLoginPassFailure()
	{
		$user = 'fred';
		$pass = 'derf';
		$cfg = $this->getModelMockBuilder('filetransfer/protocol_types_sftp_config')
			->disableOriginalConstructor()
			->setMethods(array('getUsername', 'getPassword'))
			->getMock();
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
			->setMethods(array('getData', 'getConfigModel'))
			->getMock();
		$sftp
			->expects($this->any())
			->method('getData')
			->with($this->identicalTo('con'))
			->will($this->returnValue($netSftp));
		$sftp
			->expects($this->any())
			->method('getConfigModel')
			->will($this->returnValue($cfg));

		$sftpRef = new ReflectionObject($sftp);
		$loginMethod = $sftpRef->getMethod('_loginPass');
		$loginMethod->setAccessible(true);

		$this->setExpectedException('EbayEnterprise_FileTransfer_Exception_Authentication');
		$loginMethod->invoke($sftp);
	}
	/**
	 * Test that getPrivateKey loads the key string from config and
	 * returns it loaded into a Crypt_RSA container object.
	 * @mock Crypt_RSA::loadKey to check args, make sure correct key is loaded
	 * @mock EbayEnterprise_FileTransfer_Protocol_Types_Sftp_Config::getPrivateKey stub config value
	 * @stub EbayEnterprise_FileTransfer_Protocol_Types_Sftp::getConfigModel get the stubbed config
	 * @stub EbayEnterprise_FileTransfer_Protocol_Types_Sftp::getRsa get the mocked Crypt_RSA object
	 */
	public function testGetPrivateKey()
	{
		$key = 'g minor';
		$rsa = $this->getMock('Crypt_RSA', array('loadKey'));
		$cfg = $this->getModelMockBuilder('filetransfer/protocol_types_sftp_config')
			->disableOriginalConstructor()
			->setMethods(array('getPrivateKey'))
			->getMock();
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
	 * @mock EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp_Config::getUsername stub the config value
	 * @mock Crypt_RSA stub instance used as the private key used to login with
	 * @mock Net_SFTP::login make sure the instance is logged in with the proper username and key
	 * @mock Net_SFTP::disconnect called by __destruct, just make sure it doesn't error out
	 * @mock TryeAction_FileTransfer_Model_Protocol_Types_Sftp::getData swap out the mock Net_SFTP instance
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
		$cfg = $this->getModelMockBuilder('filetransfer/protocol_types_sftp_config')
			->disableOriginalConstructor()
			->setMethods(array('getUsername'))
			->getMock();
		$netSftp = $this
			->getMockBuilder('Net_SFTP')
			->setMethods(array('login', 'disconnect'))
			->disableOriginalConstructor()
			->getMock();
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getData', 'getConfigModel', '_getPrivateKey'))
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
			->method('getData')
			->with($this->identicalTo('con'))
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
	 * @mock EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp_Config::getUsername stub the config value
	 * @mock Crypt_RSA stub instance used as the private key used to login with
	 * @mock Net_SFTP::login make sure the instance is logged in with the proper username and key
	 * @mock Net_SFTP::disconnect called by __destruct, just make sure it doesn't error out
	 * @mock TryeAction_FileTransfer_Model_Protocol_Types_Sftp::getData swap out the mock Net_SFTP instance
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
		$cfg = $this->getModelMockBuilder('filetransfer/protocol_types_sftp_config')
			->disableOriginalConstructor()
			->setMethods(array('getUsername'))
			->getMock();
		$netSftp = $this
			->getMockBuilder('Net_SFTP')
			->setMethods(array('login', 'disconnect'))
			->disableOriginalConstructor()
			->getMock();
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getData', 'getConfigModel', '_getPrivateKey'))
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
			->method('getData')
			->with($this->identicalTo('con'))
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

		$this->setExpectedException('EbayEnterprise_FileTransfer_Exception_Authentication');
		$loginKey->invoke($sftp);
	}
	public function testGetRemotePath()
	{
		$rel = 'foo';
		$rem = '/foo/bar';
		$cfg = $this->getModelMockBuilder('filetransfer/protocol_types_sftp_config')
			->disableOriginalConstructor()
			->setMethods(array('getRemotePath'))
			->getMock();
		$cfg
			->expects($this->once())
			->method('getRemotePath') // similar method name, different class
			->will($this->returnValue($rem));
		$sftp = $this
			->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->setMethods(array('getConfigModel'))
			->disableOriginalConstructor()
			->getMock();
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
