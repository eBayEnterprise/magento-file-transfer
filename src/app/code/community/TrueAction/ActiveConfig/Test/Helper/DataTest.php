<?php
class TrueAction_ActiveConfig_Test_Helper_DataTest extends EcomDev_PHPUnit_Test_Case_Config
{
	/**
	 * @loadFixture config.yaml
	 */
	public function testGetProtocolModel()
	{
		$model = Mage::helper('filetransfer')
			->getProtocolModel('testsection/testgroup');
		$this->assertSame(
			'TrueAction_FileTransfer_Model_Protocol_Types_Ftp',
			get_class($model)
		);
	}

	/**
	 * @loadFixture config.yaml
	 */
	public function testGetProtocolModelWithProtocol()
	{
		$model = Mage::helper('filetransfer')
			->getProtocolModel('testsection/testgroup', 'ftp');
		$this->assertSame(
			'TrueAction_FileTransfer_Model_Protocol_Types_Ftp',
			get_class($model)
		);
		$model = Mage::helper('filetransfer')
			->getProtocolModel('testsection/testgroup', 'ftps');
		$this->assertSame(
			'TrueAction_FileTransfer_Model_Protocol_Types_Ftps',
			get_class($model)
		);
		$model = Mage::helper('filetransfer')
			->getProtocolModel('testsection/testgroup', 'sftp');
		$this->assertSame(
			'TrueAction_FileTransfer_Model_Protocol_Types_Sftp',
			get_class($model)
		);
	}

}