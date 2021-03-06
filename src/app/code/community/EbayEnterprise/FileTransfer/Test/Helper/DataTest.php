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

class EbayEnterprise_FileTransfer_Test_Helper_DataTest extends EbayEnterprise_FileTransfer_Test_Abstract
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
		$this->replaceByMock(
			'model',
			'filetransfer/protocol_types_sftp',
			$this->getModelMockBuilder('filetransfer/protocol_types_sftp')
				->disableOriginalConstructor()
				->getMock()
		);
	}
	/**
	 * My go-to - just instantiate and assert 'it is what it is'
	 *
	 */
	public function testConstruct()
	{
		$this->assertInstanceOf('EbayEnterprise_FileTransfer_Helper_Data', $this->_helper);
	}

	/**
	 * Get list of all protocols we support. Notice only 0th element is examined,
	 * as of this writing only the 0th element - sftp - is supported.
	 */
	public function testGetProtocolCodes()
	{
		$codesArray = $this->_helper->getProtocolCodes();
		$this->assertSame('sftp', $codesArray[0]);
	}

	/**
	 * Tests the various config getters
	 *
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
	 * Test getInitData(); loads protocol from defined-config-path fixture
	 *
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
	 * @expectedException Mage_Core_Exception
	 */
	public function testGetInitDataFail()
	{
		$this->_helper->getInitData(null);
	}

	/**
	 * Test failure path of getProtocolModel, by sending in an unsupported protocol
	 *
	 * @expectedException EbayEnterprise_FileTransfer_Model_Protocol_Exception
	 */
	public function testInvalidProtocolModel()
	{
		$this->_helper->getProtocolModel(
			self::MAGE_CONFIG_PATH,
			'SomeStrangeAndUnnaturalValueForAProtocol8D82507D585D579A01235E6A51288E0A1B186EED'
		);
	}
	/**
	 * Tests by pulling protocol argument from config
	 *
	 * @loadFixture config.yaml
	 */
	public function testGetProtocolModel()
	{
		$model = $this->_helper
			->getProtocolModel('testsection/testgroup');
		$this->assertInstanceOf(
			'EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp',
			$model
		);
	}

	/**
	 * Tests with specifically-named argument for protocol
	 *
	 * @loadFixture config.yaml
	 */
	public function testGetProtocolModelWithProtocol()
	{
		$model = $this->_helper
			->getProtocolModel('testsection/testgroup', 'sftp');
		$this->assertInstanceOf(
			'EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp',
			$model
		);
	}

}
