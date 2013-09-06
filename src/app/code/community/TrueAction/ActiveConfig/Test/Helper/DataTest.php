<?php
class TrueAction_ActiveConfig_Test_Helper_DataTest extends EcomDev_PHPUnit_Test_Case_Config
{
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
		$this->assertSame(
			'TrueAction_FileTransfer_Model_Protocol_Types_Sftp',
			get_class($model)
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
		$this->assertSame(
			'TrueAction_FileTransfer_Model_Protocol_Types_Sftp',
			get_class($model)
		);
	}

}
