<?php
class TrueAction_FileTransfer_Helper_Data extends Mage_Core_Helper_Abstract
{
	$_configErrorClass = Exception;

	public function sendFile($localFile, $remoteFile, $configPath, $store=null)
	{
		try {
			$protocol = $this->getProtocolModel($fileTransferConfig);
			$protocol->sendFile($localFile, $remoteFile);
		} catch (Exception $e) {
			Mage::log("filetransfer send error:". $e->getMessage());
		}
	}

	public function getFile($remoteFile, $localFile, $configPath, $store=null)
	{
		try {
			$protocol = $this->getProtocolModel($configPath, $store);
			$protocol->getFile($localFile, $remoteFile);
		} catch (Exception $e) {
			Mage::log("filetransfer get error:". $e->getMessage());
		}
	}


	public function getProtocolModel($configPath, $store=null)
	{
		$protocol = Mage::getStoreConfig(
			sprintf('%s/filetransfer_protocol', $configPath),
			$store
		);
		return Mage::getModel(
			'filetransfer/protocol_'.$protocol,
			array('store'=>$store, 'config_path'=>$configPath)
		);
	}
}