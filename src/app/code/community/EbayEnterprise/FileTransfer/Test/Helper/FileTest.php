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


class EbayEnterprise_FileTransfer_Test_Helper_FileTest
	extends EbayEnterprise_FileTransfer_Test_Abstract
{
	/**
	 * Move the file at the given path to the speficied directory, creating any
	 * necessary target directories in the process.
	 * @test
	 */
	public function testMvToDir()
	{
		$srcFile = '/abs//path/to/current/file.xml';
		$cleanSrcFile = '/abs/path/to/current/file.xml';
		$targetPath = 'target/dir//file.xml';
		$cleanTargetPath = '/Mage/var/target/dir/file.xml';
		$targetDir = '/Mage/var/target/dir';
		$file = $this->getMockBuilder('Varien_Io_File')
			->setMethods(array(
				'__destruct', 'open', 'checkAndCreateFolder', 'getCleanPath',
				'fileExists', 'mv',
			))
			->getMock();
		$this->replaceByMock('model', 'Varien_Io_File', $file);

		$file->expects($this->once())
			->method('open')
			->with($this->identicalTo(array('path' => Mage::getBaseDir('var'))))
			->will($this->returnValue(true));
		$file->expects($this->exactly(2))
			->method('getCleanPath')
			->will($this->returnValueMap(array(
				array($srcFile, $cleanSrcFile),
				array(Mage::getBaseDir('var') . DS . $targetPath, $cleanTargetPath),
			)));
		$file->expects($this->once())
			->method('fileExists')
			->with($this->identicalTo($cleanSrcFile), $this->identicalTo(true))
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('checkAndCreateFolder')
			->with($this->identicalTo($targetDir), $this->identicalTo(0777))
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('mv')
			->with(
				$this->identicalTo($cleanSrcFile),
				$this->identicalTo($cleanTargetPath)
			)
			->will($this->returnValue(true));
		$this->assertSame(
			true,
			Mage::helper('filetransfer/file')->mvToDir($srcFile, $targetPath)
		);
	}
	/**
	 * If an absolute target path is given, an exception should be thrown.
	 * @test
	 */
	public function testMvToDirAbsoluteTargetPath()
	{
		$srcFile = '/abs//path/to/current/file.xml';
		$cleanSrcFile = '/abs/path/to/current/file.xml';
		$targetPath = '/target/dir//file.xml';
		$file = $this->getMockBuilder('Varien_Io_File')
			->setMethods(array(
				'__destruct', 'open', 'checkAndCreateFolder', 'getCleanPath', 'fileExists', 'mv',
			))
			->getMock();
		$this->replaceByMock('model', 'Varien_Io_File', $file);

		$file->expects($this->once())
			->method('open')
			->with($this->identicalTo(array('path' => Mage::getBaseDir('var'))))
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('getCleanPath')
			->will($this->returnValueMap(array(
				array($srcFile, $cleanSrcFile),
			)));
		$file->expects($this->once())
			->method('fileExists')
			->with($this->identicalTo($cleanSrcFile), $this->identicalTo(true))
			->will($this->returnValue(true));
		// When given an absolute path, don't attempt to create the target
		// directory or move the file.
		$file->expects($this->never())
			->method('checkAndCreateFolder');
		$file->expects($this->never())
			->method('mv');
		$this->setExpectedException('EbayEnterprise_FileTransfer_Exception_Configuration', "Path must be relative, given {$targetPath}");
		Mage::helper('filetransfer/file')->mvToDir($srcFile, $targetPath);
	}
	/**
	 * Don't attempt to move a file that doesn't exist - should throw an exception.
	 * @test
	 */
	public function testMvToDirNonExistentFile()
	{
		$srcFile = '/abs//path/to/current/file.xml';
		$cleanSrcFile = '/abs/path/to/current/file.xml';
		$targetPath = 'target/dir/file.xml';
		$file = $this->getMockBuilder('Varien_Io_File')
			->setMethods(array(
				'__destruct', 'open', 'checkAndCreateFolder', 'getCleanPath', 'fileExists', 'mv',
			))
			->getMock();
		$this->replaceByMock('model', 'Varien_Io_File', $file);

		$file->expects($this->once())
			->method('open')
			->with($this->identicalTo(array('path' => Mage::getBaseDir('var'))))
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('getCleanPath')
			->will($this->returnValueMap(array(
				array($srcFile, $cleanSrcFile),
			)));
		$file->expects($this->once())
			->method('fileExists')
			->with($this->identicalTo($cleanSrcFile), $this->identicalTo(true))
			->will($this->returnValue(false));
		// Don't attempt to create directories or move a file when the source file
		// doesn't exist.
		$file->expects($this->never())
			->method('checkAndCreateFolder');
		$file->expects($this->never())
			->method('mv');
		$this->setExpectedException('EbayEnterprise_FileTransfer_Exception_Configuration', "Cannot move {$cleanSrcFile}: No such file or directory");
		Mage::helper('filetransfer/file')->mvToDir($srcFile, $targetPath);
	}
	/**
	 * If the target directory cannot be created, the Varien_Io_File will throw
	 * an exception, the exception should bubble up for higher level processes
	 * to deal with.
	 * @test
	 */
	public function testMvToDirUnableToCreateTargetDir()
	{
		$srcFile = '/abs//path/to/current/file.xml';
		$cleanSrcFile = '/abs/path/to/current/file.xml';
		$targetPath = 'target/dir//file.xml';
		$cleanTargetPath = '/Mage/var/target/dir/file.xml';
		$targetDir = '/Mage/var/target/dir';
		$file = $this->getMockBuilder('Varien_Io_File')
			->setMethods(array(
				'__destruct', 'open', 'checkAndCreateFolder', 'getCleanPath', 'fileExists', 'mv',
			))
			->getMock();
		$this->replaceByMock('model', 'Varien_Io_File', $file);

		$file->expects($this->once())
			->method('open')
			->with($this->identicalTo(array('path' => Mage::getBaseDir('var'))))
			->will($this->returnValue(true));
		$file->expects($this->exactly(2))
			->method('getCleanPath')
			->will($this->returnValueMap(array(
				array($srcFile, $cleanSrcFile),
				array(Mage::getBaseDir('var') . DS . $targetPath, $cleanTargetPath),
			)));
		$file->expects($this->once())
			->method('fileExists')
			->with($this->identicalTo($cleanSrcFile), $this->identicalTo(true))
			->will($this->returnValue(true));
		$file->expects($this->once())
			->method('checkAndCreateFolder')
			->with($this->identicalTo($targetDir))
			->will($this->throwException(new Exception()));
		// If the target dir doesn't exist or cannot be created, the move shouldn't
		// be attempted.
		$file->expects($this->never())
			->method('mv');

		$this->setExpectedException('EbayEnterprise_FileTransfer_Exception_Configuration', "Target directory '{$targetDir}' does not exist and could not be created.");
		Mage::helper('filetransfer/file')->mvToDir($srcFile, $targetPath);
	}
}