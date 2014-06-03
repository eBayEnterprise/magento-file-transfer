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

class EbayEnterprise_ActiveConfig_Test_Helper_DataTest extends EcomDev_PHPUnit_Test_Case
{

	public function setUp()
	{
		$this->replaceByMock(
			'model',
			'filetransfer/protocol_types_sftp',
			$this->getModelMockBuilder('filetransfer/protocol_types_sftp')
				->disableOriginalConstructor()
				->getMock()
		);
	}
	/**
	 * Tests by pulling protocol argument from config
	 *
	 * @test
	 * @loadFixture config.yaml
	 */
	public function testGetProtocolModel()
	{
		$model = Mage::helper('filetransfer')
			->getProtocolModel('testsection/testgroup');
		$this->assertInstanceOf(
			'EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp',
			$model
		);
	}

	/**
	 * Tests with specifically-named argument for protocol
	 *
	 * @test
	 * @loadFixture config.yaml
	 */
	public function testGetProtocolModelWithProtocol()
	{
		$model = Mage::helper('filetransfer')
			->getProtocolModel('testsection/testgroup', 'sftp');
		$this->assertInstanceOf(
			'EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp',
			$model
		);
	}

}
