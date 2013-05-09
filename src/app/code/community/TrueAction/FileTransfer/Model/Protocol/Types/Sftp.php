<?php
/*
TrueAction_FileTransfer_Model_Protocol_Types_Sftp

concrete configuration generator for the ftp protocol.
 */
class TrueAction_FileTransfer_Model_Protocol_Types_Sftp extends TrueAction_FileTransfer_Model_Protocol_Types_Ftp
{
	protected $_fieldMap = array(
		'filetransfer_sftp_username'    => 'username',
		'filetransfer_sftp_password'    => 'password',
		'filetransfer_sftp_host'        => 'host',
		'filetransfer_sftp_port'        => 'port',
		'filetransfer_sftp_remote_path' => 'remote_path',
	);


	public function _construct()
	{
		$this->setName('File Transfer Protocol (SSL)');
		$this->setCode('sftp');
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
				Mage::throwException("Failed to connect to 'ftp://$this->_host'.");
			} catch (Exception $e) {
				$success = false;
				Mage::logException($e);
			}
		}
		return $success;
	}
}
