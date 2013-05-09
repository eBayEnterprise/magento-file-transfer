<?php
class TrueAction_FileTransfer_Model_Adminhtml_System_Config_Source_Protocols
{
	public function toOptionArray()
	{
		return array(
			array("value"=>"ftp",  "label"=>Mage::helper('filetransfer')->__("FTP")),
			array("value"=>"sftp", "label"=>Mage::helper('filetransfer')->__("SFTP")),
		);
	}

	public function toArray()
	{
		return array(
			"ftp"  => Mage::helper('filetransfer')->__("FTP"),
			"sftp" => Mage::helper('filetransfer')->__("SFTP"),
		);
	}
}