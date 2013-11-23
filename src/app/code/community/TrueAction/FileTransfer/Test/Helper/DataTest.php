<?php
class TrueAction_FileTransfer_Test_Helper_DataTest extends TrueAction_FileTransfer_Test_Abstract
{
	const TESTBASE_DIR_NAME = 'testBase';
	const FILE1_NAME        = 'munsters.txt';
	const FILE1_CONTENTS    = 'The Munsters is an American television sitcom depicting the home life of a family of benign monsters.';
	const FILE2_NAME        = 'addams.txt';
	const MAGE_CONFIG_PATH  = 'testsection/testgroup';

	private $_vfs;

	private $_helper;

	public function setUp()
	{
		$this->_helper = Mage::helper('filetransfer');
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
	 * My go-to - just instantiate and assert 'it is what it is'
	 *
	 * @test
	 */
	public function testConstruct()
	{
		$this->assertInstanceOf('TrueAction_FileTransfer_Helper_Data', $this->_helper);
	}

	/**
	 * Take the error path of these methods, they should return false
	 *
	 * @test
	 * @dataProvider dataProvider
	 */
	public function testMethodsFail($method, $args, $exception, $message)
	{
		$this->setExpectedException($exception, $message);
		call_user_func_array(array($this->_helper, $method), $args);
	}

	/**
	 * Get list of all protocols we support. Notice only 0th element is examined,
	 * as of this writing only the 0th element - sftp - is supported.
	 * @test
	 */
	public function testGetProtocolCodes()
	{
		$codesArray = $this->_helper->getProtocolCodes();
		$this->assertSame('sftp', $codesArray[0]);
	}

	/**
	 * Tests the various config getters
	 *
	 * @test
	 * @loadFixture
	 */
	public function testConfigGetters()
	{
		$this->assertEquals('ProtocolX', $this->_helper->getDefaultProtocol());
		$this->assertEquals(9, $this->_helper->getGlobalSortOrder());
		$this->assertEquals(18, $this->_helper->getGlobalShowInDefault());
		$this->assertEquals(27, $this->_helper->getGlobalShowInWebsite());
		$this->assertEquals(36, $this->_helper->getGlobalShowInStore());
	}
	/**
	 * Test the helper pass through to a protocol model for sending files to a remote
	 *
	 * @test
	 */
	public function testSendFile()
	{
		$protocol = $this->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->disableOriginalConstructor()
			->setMethods(array('sendFile'))
			->getMock();
		$protocol->expects($this->once())
			->method('sendFile')
			->with($this->identicalTo('local'), $this->identicalTo('remote'))
			->will($this->returnValue(true));

		$helper = $this->getHelperMock('filetransfer/data', array('getProtocolModel'));
		$helper->expects($this->any())
			->method('getProtocolModel')
			->with($this->identicalTo('config/path'), $this->identicalTo(1))
			->will($this->returnValue($protocol));

		$this->assertTrue($helper->sendFile('local', 'remote', 'config/path', 1));
	}
	/**
	 * Test the helper passing through to a protocol model to get a file from the remote
	 *
	 * @test
	 */
	public function testGetFile()
	{
		$protocol = $this->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->disableOriginalConstructor()
			->setMethods(array('getFile'))
			->getMock();
		$protocol->expects($this->once())
			->method('getFile')
			->with('local', 'remote')
			->will($this->returnValue(true));

		$helper = $this->getHelperMock('filetransfer/data', array('getProtocolModel'));
		$helper->expects($this->any())
			->method('getProtocolModel')
			->with($this->identicalTo('config/path'), $this->identicalTo(1))
			->will($this->returnValue($protocol));

		$this->assertTrue($helper->getFile('local', 'remote', 'config/path', 1));
	}
	/**
	 * Test the helper passing through to a protocol model to delete a file from the remote
	 *
	 * @test
	 */
	public function testDeleteFile()
	{
		$protocol = $this->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->disableOriginalConstructor()
			->setMethods(array('deleteFile'))
			->getMock();
		$protocol->expects($this->once())
			->method('deleteFile')
			->with($this->identicalTo('remote/file'))
			->will($this->returnValue(true));

		$helper = $this->getHelperMock('filetransfer/data', array('getProtocolModel'));
		$helper->expects($this->any())
			->method('getProtocolModel')
			->with($this->identicalTo('config/path'), $this->identicalTo(1))
			->will($this->returnValue($protocol));

		$this->assertTrue($helper->deleteFile('remote/file', 'config/path', 1));
	}
	/**
	 * Test the helper passing through to a protocol model to get all files matching a pattern from the remote
	 *
	 * @test
	 */
	public function testGetAllFiles()
	{
		$protocol = $this->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->disableOriginalConstructor()
			->setMethods(array('getAllFiles'))
			->getMock();
		$protocol->expects($this->once())
			->method('getAllFiles')
			->with($this->identicalTo('local/dir'), $this->identicalTo('remote/dir'), $this->identicalTo('*man'))
			->will($this->returnValue(true));

		$helper = $this->getHelperMock('filetransfer/data', array('getProtocolModel'));
		$helper->expects($this->any())
			->method('getProtocolModel')
			->with($this->identicalTo('config/path'), $this->identicalTo(1))
			->will($this->returnValue($protocol));

		$this->assertTrue($helper->getAllFiles('local/dir', 'remote/dir', '*man', 'config/path', 1));
	}
	/**
	 * Test the helper passing through to a protocol model to send a string to the remote
	 *
	 * @test
	 */
	public function testSendString()
	{
		$protocol = $this->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->disableOriginalConstructor()
			->setMethods(array('sendString'))
			->getMock();
		$protocol->expects($this->once())
			->method('sendString')
			->with($this->identicalTo('dataString'), $this->identicalTo('remote/file'))
			->will($this->returnValue(true));

		$helper = $this->getHelperMock('filetransfer/data', array('getProtocolModel'));
		$helper->expects($this->any())
			->method('getProtocolModel')
			->with($this->identicalTo('config/path'), $this->identicalTo(1))
			->will($this->returnValue($protocol));

		$this->assertTrue($helper->sendString('dataString', 'remote/file', 'config/path', 1));
	}
	/**
	 * Test the helper passing through to a protocol model to get a string from the remote
	 *
	 * @test
	 */
	public function testGetString()
	{
		$protocol = $this->getModelMockBuilder('filetransfer/protocol_types_sftp')
			->disableOriginalConstructor()
			->setMethods(array('getString'))
			->getMock();
		$protocol->expects($this->once())
			->method('getString')
			->with($this->identicalTo('remote/file'))
			->will($this->returnValue('data string'));

		$helper = $this->getHelperMock('filetransfer/data', array('getProtocolModel'));
		$helper->expects($this->any())
			->method('getProtocolModel')
			->with($this->identicalTo('config/path'), $this->identicalTo(1))
			->will($this->returnValue($protocol));

		$this->assertSame('data string', $helper->getString('remote/file', 'config/path', 1));
	}
	/**
	 * Test getInitData(); loads protocol from defined-config-path fixture
	 *
	 * @test
	 * @loadFixture
	 */
	public function testGetInitData()
	{
		$configArray = $this->_helper->getInitData(self::MAGE_CONFIG_PATH);
		$this->assertSame('sftp', $configArray['protocol_code']);
	}

	/**
	 * Test getInitData(); loads protocol from default-config-path fixture
	 *
	 * @test
	 * @loadFixture
	 */
	public function testGetInitDataDefault()
	{
		$store = Mage::app()->getStore();
		$cc = new ReflectionProperty($store, '_configCache');
		$cc->setAccessible(true);
		$cc->setValue($store, array());
		$configArray = $this->_helper->getInitData(self::MAGE_CONFIG_PATH);
		$this->assertSame('DefaultProtocol', $configArray['protocol_code']);
	}

	/**
	 * Test getInitData() failure; no config path given
	 *
	 * @test
	 * @expectedException Mage_Core_Exception
	 */
	public function testGetInitDataFail()
	{
		$this->_helper->getInitData(null);
	}

	/**
	 * Test failure path of getProtocolModel, by sending in an unsupported protocol
	 *
	 * @test
	 * @expectedException Exception
	 */
	public function testInvalidProtocolModel()
	{
		$this->_helper->getProtocolModel(
			self::MAGE_CONFIG_PATH,
			'SomeStrangeAndUnnaturalValueForAProtocol8D82507D585D579A01235E6A51288E0A1B186EED'
		);
	}
}
