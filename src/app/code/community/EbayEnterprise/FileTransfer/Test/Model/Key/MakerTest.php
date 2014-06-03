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


class EbayEnterprise_FileTransfer_Test_Model_Key_MakerTest
	extends EcomDev_PHPUnit_Test_Case
{

	const TEST_BASE_PATH = 'testBase';
	const TEST_PREFIX = 'test';

	/**
	 * Test key paths.
	 *
	 * @test
	 */
	public function testGettingKeyPaths()
	{
		$mockFsTool = $this->getMock('Varien_Io_File', array('fileExists'));
		$mockFsTool->expects($this->any())->method('fileExists')->will($this->returnValue(false));

		$keyMaker = $this->getModelMockBuilder('filetransfer/key_maker')
			->setConstructorArgs(array(array(
				'tmp_dir' => self::TEST_BASE_PATH,
				'fs_tool' => $mockFsTool,
				'tmp_prefix' => self::TEST_PREFIX,
			)))
			->setMethods(array('_tempnam'))
			->getMock();
		$keyMaker->expects($this->exactly(2))
			->method('_tempnam')
			->with(
				$this->identicalTo(self::TEST_BASE_PATH),
				$this->identicalTo(self::TEST_PREFIX)
			)
			->will($this->onConsecutiveCalls('foo', 'bar'));

		// public key set to the first result from tempnam
		$this->assertSame('foo', $keyMaker->getPublicKeyPath());
		// private key set to the second result from tempnam
		$this->assertSame('bar', $keyMaker->getPrivateKeyPath());
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
		$pubFile = self::TEST_BASE_PATH . DS . self::TEST_PREFIX . 'pub';
		$privFile = self::TEST_BASE_PATH . DS . self::TEST_PREFIX . 'priv';
		// Mock the Varien_Io_File object, this is our FsTool for testing purposes
		$mockFsTool = $this->getMock(
			'Varien_Io_File',
			array('write', 'rm', 'open', 'fileExists')
		);
		$mockFsTool->expects($this->once())
			->method('open')
			->with($this->identicalTo(array('path' => self::TEST_BASE_PATH)))
			->will($this->returnValue(true));
		$mockFsTool->expects($this->exactly(2))
			->method('write')
			->with(
				$this->stringStartsWith(self::TEST_BASE_PATH),
				$this->logicalOr($this->identicalTo($pubKey), $this->identicalTo($privKey)),
				$this->logicalOr($this->identicalTo(0644), $this->identicalTo(0600))
			)
			->will($this->returnValue(true));
		$mockFsTool->expects($this->exactly(2))
			->method('fileExists')
			->with($this->logicalOr(
				$this->identicalTo($pubFile),
				$this->identicalTo($privFile)
			))
			->will($this->returnValue(true));
		$mockFsTool->expects($this->exactly(2))
			->method('rm')
			->with($this->logicalOr(
					$this->identicalTo($pubFile),
					$this->identicalTo($privFile)
			))
			->will($this->returnValue(true));

		$keyMaker = $this->getModelMockBuilder('filetransfer/key_maker')
			->setMethods(array('_tempnam'))
			->setConstructorArgs(array(array(
				'tmp_dir' => self::TEST_BASE_PATH,
				'fs_tool' => $mockFsTool,
				'tmp_prefix' => self::TEST_PREFIX,
			)))
			->getMock();
		$keyMaker->expects($this->exactly(2))
			->method('_tempnam')
			->with(
				$this->identicalTo(self::TEST_BASE_PATH),
				$this->identicalTo(self::TEST_PREFIX)
			)
			->will($this->onConsecutiveCalls($pubFile, $privFile));

		$keyMaker->createKeyFiles($pubKey, $privKey);
		$pubPath = $keyMaker->getPublicKeyPath();
		$privPath = $keyMaker->getPrivateKeyPath();

		$this->assertStringStartsWith(self::TEST_BASE_PATH, $pubPath);
		$this->assertStringStartsWith(self::TEST_BASE_PATH, $privPath);
		$this->assertSame($keyMaker->getPublicKeyPath(), $pubPath);
		$this->assertSame($keyMaker->getPrivateKeyPath(), $privPath);

		$keyDestroyer = new ReflectionMethod($keyMaker, '_destroyKeys');
		$keyDestroyer->setAccessible(true);
		$keyDestroyer->invoke($keyMaker);
	}

	/**
	 * Test that default data is set when none is passed to the constructor.
	 *
	 * @test
	 */
	public function testSetupWithDefaults()
	{
		$pubFile = 'pubfile';
		$privFile = 'privfile';
		$maker = $this->getModelMockBuilder('filetransfer/key_maker')
			->setMethods(array('_tempnam'))
			->getMock();
		$maker->expects($this->exactly(2))
			->method('_tempnam')
			->with(
				$this->identicalTo(Mage::getBaseDir('tmp')),
				$this->identicalTo('EbayEnterprise_FileTransfer_Model_Key_Maker')
			)
			->will($this->onConsecutiveCalls($pubFile, $privFile));

		$this->assertInstanceOf('Varien_Io_File', $maker->getFsTool());
		$this->assertStringStartsWith(Mage::getBaseDir('tmp'), $maker->getTmpDir());
		$this->assertSame('EbayEnterprise_FileTransfer_Model_Key_Maker', $maker->getTmpPrefix());
		$this->assertSame($pubFile, $maker->getPublicKeyPath());
		$this->assertSame($privFile, $maker->getPrivateKeyPath());

		// swap out the fsTool so the __destruct method doesn't...well...blow up
		$fsToolMock = $this->getMock('Varien_Io_File', array('fileExists'));
		$fsToolMock->expects($this->any())->method('fileExists')->will($this->returnValue(false));
		$maker->setFsTool($fsToolMock);
	}
}
