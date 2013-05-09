<?php
/*
TrueAction_FileTransfer_Model_Config_Ftp

concrete configuration generator for the ftp protocol.
 */
class TrueAction_FileTransfer_Model_Protocol_Ftp extends Mage_Core_Model_Abstract
{
	protected $_conn;
	protected $_auth;
	protected $_pasv;
	protected $_fieldMap = array(
		'filetransfer_ftp_username'    => 'username',
		'filetransfer_ftp_password'    => 'password',
		'filetransfer_ftp_host'        => 'host',
		'filetransfer_ftp_port'        => 'port',
		'filetransfer_ftp_remote_path' => 'remote_path',
	);


	public function _construct()
	{
		$this->setName('File Transfer Protocol');
		$this->setCode('ftp');
		// create magic getter/setters for each field
		$this->getConfig()->loadMappedFields($this->_fieldMap);
	}

	public function sendFile($localFile, $remoteFile)
	{
		$remotePath = Mage::helper('filetransfer')->normalPaths(
			$this->getRemotePath($storeView),
			basename($localFile)
		);
		// connect to ftp server
		$isSuccess = $this->connect();
		// login to ftp connection
		$isSuccess = $isSuccess && $this->login();
		// check to see if ftp connect is in passive mode
		$isSuccess = $isSuccess && $this->isPassive();
		// Transfer file
		$isSuccess = $isSuccess && $this->transfer($remotePath, $localPath);
		// close ftp connection
		$this->close();
		return $isSuccess;
	}

	public function getFile($remoteFile, $localFile)
	{

	}

	public function setHost($host='')
	{
		$this->getConfig()->setHost($host);
	}

	public function setPort($port='21')
	{
		$this->getConfig()->setPort($port);
	}

	public function setUsername($username='')
	{
		$this->getConfig()->setUsername($username);
	}

	public function setPassword($password='')
	{
		$this->getConfig()->setPassword($password);
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
		if (!$this->_conn = ftp_connect($config->getHost(), $config->getPort)){
			try{
				Mage::throwException("Failed to connect to 'ftp://$this->_host'.");
			} catch (Exception $e) {
				$success = false;
				Mage::logException($e);
			}
		}
		return $success;
	}

	/**
	 * Login to FTP Server.
	 *
	 * @param user
	 * @param pass
	 */
	public function login()
	{
		$success = true;
		$config = $this->getConfig();
		if (!$this->_auth = ftp_login($this->_conn, $config->getUsername, $config->getPassword)){
			try{
				Mage::throwException("Failed to authenticate to 'ftp://$this->_host@$this->_host'.");
			} catch (Exception $e) {
				$success = false;
				Mage::logException($e);
			}
		}
		return $success;
	}

	/**
	 * is FTP Connection Passive .
	 *
	 * @param host
	 * @param port
	 */
	public function isPassive()
	{
		$success = true;
		if (!$this->_pasv = ftp_pasv($this->_conn, true)){
			try{
				Mage::throwException("Failed to switch to passive mode on 'ftp://$this->_host'.");
			} catch (Exception $e) {
				$success = false;
				Mage::logException($e);
			}
		}
		return $success;
	}

	/**
	 * Transfer files from current local server to destination remote server.
	 *
	 * @param String remoteFile
	 * @param String localFile
	 */
	public function transfer($remoteFile, $localFile)
	{
		$success = true;
		Mage::log("Connected to ftp://$this->_username@$this->_host");
		if (!$up = ftp_put($this->_conn, $remoteFile, $localFile, FTP_BINARY)) {
			try{
				Mage::throwException("Failed to upload '$localFile' to 'ftp://$this->_host'.");
			} catch (Exception $e) {
				$success = false;
				Mage::logException($e);
			}
		}else{
			Mage::log("Uploaded '$localFile' to 'ftp://$this->_host'.");
		}

		return $success;
	}

	/**
	 * Close FTP Connection.
	 *
	 * @param host
	 * @param port
	 */
	public function close()
	{
		if ($this->_conn){
			ftp_close($this->_conn);
		}
	}
}
