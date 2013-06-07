<?php
/*
TrueAction_FileTransfer_Model_Protocol_Types_ftps

concrete configuration generator for the ftp protocol.
 */
class TrueAction_FileTransfer_Model_Protocol_Types_Ftps extends TrueAction_FileTransfer_Model_Protocol_Types_Ftp
{
	public function _construct()
	{
		parent::_construct();
		$this->setName('File Transfer Protocol (SSL)');
	}

	/**
	 * Connect to FTP Server.
	 *
	 * @param host
	 * @param port
	 */
	public function connect()
	{
		$config = $this->getConfig();
		$success = true;
		$this->_conn = ftp_ssl_connect($config->getHost(), $config->getPort());
		if (!$this->_conn){
			try{
				Mage::throwException("Failed to connect to 'ftps://$this->_host'.");
			} catch (Exception $e) {
				$success = false;
				Mage::logException($e);
			}
		}
		return $success;
	}
}
