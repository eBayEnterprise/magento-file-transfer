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

class EbayEnterprise_FileTransfer_Test_Model_Protocol_Types_Sftp_ConfigTest extends EcomDev_PHPUnit_Test_Case
{
	public function setUp()
	{
		$this->class = new ReflectionClass(
			'EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp_Config'
		);
		$this->loadMappedFields = $this->class->getMethod('loadMappedFields');
		$this->loadMappedFields->setAccessible(true);
		$this->importOptions = new Varien_Simplexml_Config(
			'<filetransfer>
				<sort_order>190</sort_order>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
				<sftp></sftp>
			</filetransfer>'
		);
	}
	/**
	 * verify the constructor sets the protocol code for the model.
	 */
	public function testConstructorProtocolCode()
	{
		$config = $this->getModelMockBuilder('filetransfer/protocol_types_sftp_config')
			->disableOriginalConstructor()
			->setMethods(array('setProtocolCode', '_validateProtocolCode'))
			->getMock();
		$config->expects($this->once())
			->method('setProtocolCode')
			->with($this->identicalTo('sftp'))
			->will($this->returnSelf());
		EcomDev_Utils_Reflection::invokeRestrictedMethod($config, '_construct');
	}

	/**
	 * verify the base fields are generated as expected.
	 */
	public function testGetBaseFields()
	{
		$config = $this->getModelMockBuilder('filetransfer/protocol_types_sftp_config')
			->disableOriginalConstructor()
			->setMethods(array('getProtocolCode'))
			->getMock();
		$config->expects($this->once())
			->method('getProtocolCode')
			->will($this->returnValue('foo'));

		$result = $config->getBaseFields();
		$this->assertInstanceOf('Varien_Simplexml_Config', $result);

		$resultNode = $result->getNode();
		$this->assertSame('fields', $resultNode->getName());

		$paths = array(
			'filetransfer_protocol/label[.="Protocol"]',
		);
		foreach ($paths as $path) {
			$this->assertNotEmpty(
				$resultNode->xpath($path),
				"path: '$path'"
			);
		}
	}
}
