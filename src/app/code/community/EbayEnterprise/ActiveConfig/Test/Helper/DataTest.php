<?php
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
