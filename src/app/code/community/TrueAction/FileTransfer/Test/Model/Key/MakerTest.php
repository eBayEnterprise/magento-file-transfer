<?php

class TrueAction_FileTransfer_Test_Model_Key_MakerTest
	extends EcomDev_PHPUnit_Test_Case
{

	const TEST_BASE_PATH = 'testBase';

	/**
	 * Test key paths.
	 *
	 * @test
	 */
	public function testGettingKeyPaths()
	{
		$mockFsTool = $this->getMock('Varien_Io_File', array(
			'checkAndCreateFolder', 'cd', 'ls', 'rm', 'rmdir', 'fileExists'
		));
		$mockFsTool->expects($this->any())
			->method('checkAndCreateFolder')
			->will($this->returnValue(true));
		$mockFsTool->expects($this->any())
			->method('cd')
			->will($this->returnValue(true));
		$mockFsTool->expects($this->any())
			->method('ls')
			->will($this->returnValue(array()));
		$mockFsTool->expects($this->any())
			->method('rm')
			->will($this->returnValue(true));
		$mockFsTool->expects($this->any())
			->method('rmdir')
			->will($this->returnValue(true));

		$keyMaker = Mage::getModel('filetransfer/key_maker', array(
			'base_dir' => self::TEST_BASE_PATH,
			'fs_tool' => $mockFsTool,
		));
		$this->assertStringStartsWith(self::TEST_BASE_PATH, $keyMaker->getPublicKeyPath());
		$this->assertStringStartsWith(self::TEST_BASE_PATH, $keyMaker->getPrivateKeyPath());
		// these two may seem odd but as the file names are all generated, need to ensure
		// they are only generated once so we can find the files after creating them
		$this->assertSame($keyMaker->getPublicKeyPath(), $keyMaker->getPublicKeyPath());
		$this->assertSame($keyMaker->getPrivateKeyPath(), $keyMaker->getPrivateKeyPath());

	}

	/**
	 * Test the creation and destruction of the keys.
	 *
	 * @test
	 */
	public function testCreateKeyFiles()
	{
		$pubKey = 'foo key';
		$privKey = 'bar key';
		// Mock the Varien_Io_File object, this is our FsTool for testing purposes
		$mockFsTool = $this->getMock(
			'Varien_Io_File',
			array('cd', 'checkAndCreateFolder', 'write', 'ls', 'rm', 'rmdir', 'open', 'fileExists')
		);
		$mockFsTool->expects($this->once())
			->method('open')
			->with($this->identicalTo(array('path' => self::TEST_BASE_PATH)))
			->will($this->returnValue(true));
		$mockFsTool->expects($this->once())
			->method('cd')
			->with($this->identicalTo(self::TEST_BASE_PATH))
			->will($this->returnValue(true));
		$mockFsTool->expects($this->any())
			->method('checkAndCreateFolder')
			->with($this->identicalTo(self::TEST_BASE_PATH), $this->identicalTo(0644))
			->will($this->returnValue(true));
		$mockFsTool->expects($this->exactly(2))
			->method('write')
			->with(
				$this->stringStartsWith(self::TEST_BASE_PATH),
				$this->logicalOr($this->identicalTo($pubKey), $this->identicalTo($privKey)),
				$this->logicalOr($this->identicalTo(0644), $this->identicalTo(0600))
			)
			->will($this->returnValue(true));
		$mockFsTool->expects($this->exactly(1))
			->method('fileExists')
			->with($this->stringStartsWith(self::TEST_BASE_PATH), $this->isFalse())
			->will($this->returnValue(true));
		$mockFsTool->expects($this->exactly(1))
			->method('ls')
			->will($this->returnValue(array(
				array('text' => 'fake_pub_key_filename',),
				array('text' => 'fake_priv_key_filename'),
			)));
		$mockFsTool->expects($this->exactly(2))
			->method('rm')
			->with($this->logicalOr(
					$this->stringContains('fake_pub_key_filename'),
					$this->identicalTo('fake_priv_key_filename')
			))
			->will($this->returnValue(true));
		$mockFsTool->expects($this->exactly(1))
			->method('rmdir')
			->with($this->stringStartsWith(self::TEST_BASE_PATH))
			->will($this->returnValue(true));

		$keyMaker = Mage::getModel('filetransfer/key_maker', array(
			'base_dir' => self::TEST_BASE_PATH,
			'fs_tool' => $mockFsTool,
		));
		$keyMaker->createKeyFiles($pubKey, $privKey);
		$pubPath = $keyMaker->getPublicKeyPath();
		$privPath = $keyMaker->getPrivateKeyPath();

		$this->assertStringStartsWith(self::TEST_BASE_PATH, $pubPath);
		$this->assertStringStartsWith(self::TEST_BASE_PATH, $privPath);
		$this->assertSame($keyMaker->getPublicKeyPath(), $pubPath);
		$this->assertSame($keyMaker->getPrivateKeyPath(), $privPath);
		unset($keyMaker);
	}

	/**
	 * Test that default data is set when none is passed to the constructor.
	 *
	 * @test
	 */
	public function testSetupWithDefaults()
	{
		$maker = Mage::getModel('filetransfer/key_maker');
		$this->assertInstanceOf('Varien_Io_File', $maker->getFsTool());
		$this->assertStringStartsWith(Mage::getBaseDir('tmp'), $maker->getBaseDir());

		// swap out the fsTool so the __destruct method doesn't...well...blow up
		$fsToolMock = $this->getMock('Varien_Io_File', array('fileExists'));
		$fsToolMock->expects($this->any())->method('fileExists')->will($this->returnValue(false));
		$maker->setFsTool($fsToolMock);
	}

	/**
	 * This test will actually write to the file system and is a bit more
	 * integrations-test-ish than is desirable. Skipping the test but leaving
	 * it in place in case we ever decide to want such tests.
	 *
	 * @test
	 */
	public function testActualFileAccess()
	{
		$this->markTestSkipped('Too much of an integration test - hits the filesystem. Not a unit test so skipping');

		$pubKey = 'public key file contents';
		$privKey = 'private key file contents';
		$baseDir = Mage::getBaseDir('tmp');

		$keyMaker = Mage::getModel('filetransfer/key_maker');
		$this->assertTrue($keyMaker->createKeyFiles($pubKey, $privKey));

		$this->assertStringStartsWith($baseDir, $keyMaker->getPublicKeyPath());
		$this->assertStringStartsWith($baseDir, $keyMaker->getPrivateKeyPath());

		$this->assertSame($keyMaker->getPublicKeyPath(), $keyMaker->getPublicKeyPath());
		$this->assertSame($keyMaker->getPrivateKeyPath(), $keyMaker->getPrivateKeyPath());

		$this->assertFileExists($keyMaker->getPublicKeyPath());
		$this->assertFileExists($keyMaker->getPrivateKeyPath());

		$this->assertSame($pubKey, file_get_contents($keyMaker->getPublicKeyPath()));
		$this->assertSame($privKey, file_get_contents($keyMaker->getPrivateKeyPath()));

		$this->assertTrue($keyMaker->destroyKeys());

		$this->assertFileNotExists($keyMaker->getPublicKeyPath());
		$this->assertFileNotExists($keyMaker->getPrivateKeyPath());
	}

}
