<?php
class TrueAction_FileTransfer_Test_Model_Adminhtml_System_Config_Backend_Encrypted_KeyfileTest extends EcomDev_PHPUnit_Test_Case
{
	public static $modelAlias = 'filetransfer/adminhtml_system_config_backend_encrypted_keyfile';
	public static $reflectedClass;

	public static function setUpBeforeClass()
	{
		self::$reflectedClass = new ReflectionClass(
			'TrueAction_FileTransfer_Model_Adminhtml_System_Config_Backend_Encrypted_Keyfile'
		);
	}

	/**
	 * verify the key is read and encrypted in preparation for being saved.
	 * @test
	 */
	public function testBeforeSave()
	{
		$keyText = 'fake key text';
		$testModel = $this->getModelMockBuilder(self::$modelAlias)
			->disableOriginalConstructor()
			->setMethods(array(
				'_readKey',
				'_getKeyFieldPath',
				'_getOriginalFilename',
				'_deleteUploadedFile',
				'getScope',
				'getScopeId',
				'setValue',
			))
			->getMock();
		$testModel->expects($this->once())
			->method('_readKey')
			->will($this->returnValue($keyText));
		$testModel->expects($this->once())
			->method('setValue')
			->with($this->identicalTo('orig file name'))
			->will($this->returnSelf());
		$testModel->expects($this->once())
			->method('getScope')
			->will($this->returnValue('the scope'));
		$testModel->expects($this->once())
			->method('getScopeId')
			->will($this->returnValue('scope id'));
		$testModel->expects($this->once())
			->method('_getKeyFieldPath')
			->will($this->returnValue('/section/group/filetransfer_sftp_ssh_prv_key'));
		$testModel->expects($this->once())
			->method('_getOriginalFilename')
			->will($this->returnValue('orig file name'));
		$testModel->expects($this->once())
			->method('_deleteUploadedFile')
			->will($this->returnSelf());

		$configData = $this->getModelMockBuilder('adminhtml/system_config_backend_encrypted')
			->disableOriginalConstructor()
			->setMethods(array('save', 'addData'))
			->getMock();
		$configData->expects($this->once())
			->method('addData')
			->with($this->identicalTo(array(
				'scope' => 'the scope',
				'scope_id' => 'scope id',
				'path' => '/section/group/filetransfer_sftp_ssh_prv_key',
				'value' => $keyText
			)))
			->will($this->returnSelf());
		$configData->expects($this->once())
			->method('save')
			->will($this->returnSelf());
		$this->replaceByMock('model', 'adminhtml/system_config_backend_encrypted', $configData);

		$result = EcomDev_Utils_Reflection::invokeRestrictedMethod($testModel, '_beforeSave');
		$this->assertSame($testModel, $result);
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
	 * verify the key correct path is returned.
	 * @test
	 */
	public function testGetKeyFieldPath()
	{
		$testModel = $this->getModelMockBuilder(self::$modelAlias)
			->disableOriginalConstructor()
			->setMethods(array('getPath'))
			->getMock();
		$testModel->expects($this->once())
			->method('getPath')
			->will($this->returnValue('/section/group/filetransfer_sftp_ssh_key_file'));
		$path = EcomDev_Utils_Reflection::invokeRestrictedMethod(
			$testModel,
			'_getKeyFieldPath'
		);
		$this->assertSame($path, '/section/group/filetransfer_sftp_ssh_prv_key');
	}

	/**
	 * verify the file gets deleted
	 * @test
	 * @loadFixture
	 */
	public function testDeleteUploadedFile()
	{
		$vfs = $this->getFixture()->getVfs();
		$fileUrl = $vfs->url('tmp/thefile');
		$this->assertTrue(file_exists($fileUrl));
		$testModel = $this->getModelMockBuilder(self::$modelAlias)
			->disableOriginalConstructor()
			->setMethods(array('_getTempName'))
			->getMock();
		$testModel->expects($this->once())
			->method('_getTempName')
			->will($this->returnValue($fileUrl));
		$result = EcomDev_Utils_Reflection::invokeRestrictedMethod(
			$testModel,
			'_deleteUploadedFile'
		);
		$this->assertFalse(file_exists($fileUrl));
		$this->assertSame($testModel, $result);
	}
}
