<?php
/**
 * TrueAction_FileTransfer_Test_Model_Config_Ftp
 * tests for the ftp config generator.
 */

class TrueAction_FileTransfer_Test_Model_Protocol_AbstractTest extends EcomDev_PHPUnit_Test_Case
{
	const STRING_DATA = 'The Munsters is an American television sitcom depicting the home life of a family of benign monsters.';
	const FAKE_HOST   = 'host';
	const FAKE_PORT   = '13';
	const FAKE_USER   = 'herman';
	const FAKE_PASS   = 'munster';

	public function setUp()
	{
		$this->cls = new ReflectionClass(
			'TrueAction_FileTransfer_Model_Protocol_Abstract'
		);
		$this->getCodes = $this->cls->getMethod(
			'getCodes'
		);
	}

	public function testConstructor()
	{
		$configData = array();
		$protocolCode = 'somecode';
		$configModel = $this->getModelMockBuilder('filetransfer/protocol_config')
			->disableOriginalConstructor()
			->setMethods(array('getProtocolCode'))
			->getMock();
		$configModel->expects($this->once())
			->method('getProtocolCode')
			->will($this->returnValue($protocolCode));
		$this->replaceByMock('model', 'filetransfer/protocol_config', $configModel);

		$testModel = $this->getModelMockBuilder('filetransfer/protocol_abstract')
			->disableOriginalConstructor()
			->setMethods(array('setConfigModel', 'getConfig', 'setCode', 'getConfigModel', 'sendFile', 'getFile'))
			->getMock();
		$testModel->expects($this->once())
			->method('setConfigModel')
			->with($this->identicalTo($configModel))
			->will($this->returnSelf());
		$testModel->expects($this->once())
			->method('getConfig')
			->will($this->returnValue($configData));
		$testModel->expects($this->once())
			->method('getConfigModel')
			->will($this->returnValue($configModel));
		$testModel->expects($this->once())
			->method('setCode')
			->with($this->identicalTo($protocolCode))
			->will($this->returnSelf());
		$ctor = $this->cls->getMethod('_construct');
		$ctor->setAccessible(true);
		$ctor->invoke($testModel);
	}

	/**
	 * Test concrete methods
	 *
	 * @test
	 */
	public function testConcreteMethods()
	{
		$model = $this->getMockForAbstractClass('TrueAction_FileTransfer_Model_Protocol_Abstract');
		$this->assertInstanceOf('TrueAction_FileTransfer_Model_Protocol_Abstract', $model);

		// Some setting, make sure it chains
		$chain = $model
			->setHost(self::FAKE_HOST)
			->setPort(self::FAKE_PORT)
			->setUsername(self::FAKE_USER)
			->setPassword(self::FAKE_PASS);
		$this->assertInstanceOf(get_class($model), $chain);

		// Assert sets resulted in proper gets
		$this->assertSame(self::FAKE_HOST, $model->getConfigModel()->getHost());
		$this->assertSame(self::FAKE_PORT, $model->getConfigModel()->getPort());
		$this->assertSame(self::FAKE_USER, $model->getConfigModel()->getUsername());

		// Test getDataUriFromString method
		$dataString = $model->getDataUriFromString(self::STRING_DATA);
		$this->assertStringStartsWith('data:text/plain,', $dataString);

		$dataStringTwo = TrueAction_FileTransfer_Model_Protocol_Abstract::getDataUriFromString(self::STRING_DATA);
		$this->assertStringStartsWith('data:text/plain,', $dataStringTwo);
	}

	/**
	 * @test
	 * */
	public function testListProtocols()
	{
		$ls = $this->getCodes->invoke(null);
		$this->assertContains('sftp', $ls);
		$this->assertNotContains('.', $ls);
		$this->assertNotContains('..', $ls);
		$lsTwo = $this->getCodes->invoke(null);
		$this->assertSame($ls, $lsTwo);
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testExceptionMethods($method, $exception, $message)
	{
		$config = Mage::getModel('filetransfer/protocol_config');
		$config->setData(array(
			'host' => 'somehost.com',
			'user' => 'someuser',
			'protocol_code' => 'proto',
			'remote_path' => 'remote/path'
		));
		$this->setExpectedException($exception, $message);
		$methods = array('sendFile, getFile');
		$model = $this->getModelMock('filetransfer/protocol_abstract', $methods, true);
		$model->setConfigModel($config);
		$fn = new ReflectionMethod($model, $method);
		$fn->setAccessible(true);
		$fn->invoke($model, 'foo');
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testExceptionMethodsCustomMessage($method, $exception)
	{
		$config = Mage::getModel('filetransfer/protocol_config');
		$config->setData(array(
			'host' => 'somehost.com',
			'user' => 'someuser',
			'protocol_code' => 'proto',
			'remote_path' => 'remote/path'
		));
		$message = 'this is a completely custom message';
		$this->setExpectedException($exception, $message);
		$methods = array('sendFile, getFile');
		$model = $this->getModelMock('filetransfer/protocol_abstract', $methods, true);
		$model->setConfig($config);
		$fn = new ReflectionMethod($model, $method);
		$fn->setAccessible(true);
		$fn->invoke($model, $message, false);
	}
}
