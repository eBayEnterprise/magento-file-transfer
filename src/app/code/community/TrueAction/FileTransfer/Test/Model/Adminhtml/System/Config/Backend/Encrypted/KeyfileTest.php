<?php
class TrueAction_FileTransfer_Test_Model_Adminhtml_System_Config_Backend_Encrypted_KeyfileTest extends EcomDev_PHPUnit_Test_Case
{
	public static $modelAlias = 'filetransfer/adminhtml_system_config_backend_encrypted_keyfile';
	public static $reflectedClass;

	public static function setUpBeforeClass()
	{
		self::$reflectedClass = new ReflectionClass('TrueAction_FileTransfer_Model_Adminhtml_System_Config_Backend_Encrypted_Keyfile');
	}

	/**
	 * verify the key is read and encrypted in preparation for being saved.
	 * @test
	 */
	public function testBeforeSave()
	{
		$keyText = 'fake key text';
		$encryptedKey = 'sfljalfdjlajflsdfja;fafjdlsdfjl';
		$testModel = $this->getModelMockBuilder(self::$modelAlias)
			->disableOriginalConstructor()
			->setMethods(array('_readKey', '_encryptKey', 'setValue'))
			->getMock();
		$testModel->expects($this->once())
			->method('_readKey')
			->will($this->returnValue($keyText));
		$testModel->expects($this->once())
			->method('setValue')
			->with($this->identicalTo($encryptedKey))
			->will($this->returnSelf());

		$helper = $this->getHelperMockBuilder('core/data')
			->disableOriginalConstructor()
			->setMethods(array('encrypt'))
			->getMock();
		$helper->expects($this->once())
			->method('encrypt')
			->with($this->identicalTo($keyText))
			->will($this->returnValue($encryptedKey));
		$this->replaceByMock('helper', 'core', $helper);

		$method = self::$reflectedClass->getMethod('_beforeSave');
		$method->setAccessible(true);
		$this->assertSame($testModel, $method->invoke($testModel));
	}

	/**
	 * verify the correct calls are made to get the key from the uploaded file.
	 * @test
	 */
	public function testReadKey()
	{
		$keyText = 'fake key text';
		$filePath = '/path/to/key_file';
		$testModel = $this->getModelMockBuilder(self::$modelAlias)
			->disableOriginalConstructor()
			->setMethods(array('_getTempName', '_fileGetContents'))
			->getMock();
		$testModel->expects($this->once())
			->method('_getTempName')
			->will($this->returnValue($filePath));
		$testModel->expects($this->once())
			->method('_fileGetContents')
			->with($this->identicalTo($filePath))
			->will($this->returnValue($keyText));
		$method = self::$reflectedClass->getMethod('_readKey');
		$method->setAccessible(true);
		$this->assertSame($keyText, $method->invoke($testModel));
	}

	/**
	 * verify the key is decrypted after being loaded.
	 * @test
	 */
	public function testAfterLoad()
	{
		$keyText = 'fake key text';
		$encryptedKey = 'sfljalfdjlajflsdfja;fafjdlsdfjl';
		$testModel = $this->getModelMockBuilder(self::$modelAlias)
			->disableOriginalConstructor()
			->setMethods(array('getValue', 'setValue'))
			->getMock();
		$testModel->expects($this->once())
			->method('getValue')
			->will($this->returnValue($encryptedKey));
		$testModel->expects($this->once())
			->method('setValue')
			->with($this->identicalTo($keyText))
			->will($this->returnSelf());

		$helper = $this->getHelperMockBuilder('core/data')
			->disableOriginalConstructor()
			->setMethods(array('decrypt'))
			->getMock();
		$helper->expects($this->once())
			->method('decrypt')
			->with($this->identicalTo($encryptedKey))
			->will($this->returnValue($keyText));
		$this->replaceByMock('helper', 'core', $helper);

		$method = self::$reflectedClass->getMethod('_afterLoad');
		$method->setAccessible(true);
		$this->assertSame($testModel, $method->invoke($testModel));
	}
}
