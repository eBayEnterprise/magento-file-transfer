<?php
/*
TrueAction_FileTransfer_Model_Protocol_Types_ftps

concrete configuration generator for the ftp protocol.
 */
class TrueAction_FileTransfer_Model_Protocol_Types_Ftps extends TrueAction_FileTransfer_Model_Protocol_Types_Ftp
{
	protected $_fieldMap = array(
		'filetransfer_ftps_username'    => 'username',
		'filetransfer_ftps_password'    => 'password',
		'filetransfer_ftps_host'        => 'host',
		'filetransfer_ftps_port'        => 'port',
		'filetransfer_ftps_remote_path' => 'remote_path',
	);


	public function _construct()
	{
		$this->setName('File Transfer Protocol (SSL)');
		$this->setCode('ftps');
		// create magic getter/setters for each field
		$this->getConfig()->loadMappedFields($this->_fieldMap);
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