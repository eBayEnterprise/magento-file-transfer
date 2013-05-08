<?php
class TrueAction_FileTransfer_Model_Adminhtml_System_Config_Source_Protocols
{
	public function toOptionArray()
	{
		return array(
			array("value"=>0, "label"=>Mage::helper('filetransfer')->__("FTP")),
			array("value"=>1, "label"=>Mage::helper('filetransfer')->__("SFTP")),
		);
	}

	public function toArray()
	{
		return array(
			0 => Mage::helper('filetransfer')->__("FTP"),
			1 => Mage::helper('filetransfer')->__("SFTP"),
		);
	}
}