<?php
/*
TrueAction_FileTransfer_Model_Config_Ftp

concrete configuration generator for the ftp protocol.
 */
class TrueAction_FileTransfer_Model_Protocol_Types_Ftp extends TrueAction_FileTransfer_Model_Protocol_Abstract
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
		$remotePath = $this->normalPaths(
			$this->getConfig()->getRemotePath(),
			basename($localFile)
		);
		$localPath = $localFile;
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

	public function getFile($localFile, $remoteFile)
	{
		$remotePath = $this->normalPaths(
			$this->getConfig()->getRemotePath(),
			$remoteFile
		);
		$localPath = $this->normalPaths(
			$localFile
		);
		Mage::log("Attempting to get $remotePath");

		// connect to ftp server
		$isSuccess = $this->connect();
		// login to ftp connection
		$isSuccess = $isSuccess && $this->login();
		// check to see if ftp connect is in passive mode
		$isSuccess = $isSuccess && $this->isPassive();
		// Transfer file
		$isSuccess = $isSuccess && $this->retrieve($remotePath, $localPath);
		// close ftp connection
		$this->close();
		return $isSuccess;
	}

	public function sendString($string, $remoteFile)
	{
		$remotePath = $this->normalPaths(
			$this->getConfig()->getRemotePath(),
			basename($localFile)
		);
		$localPath = $localFile;
		// connect to ftp server
		$isSuccess = $this->connect();
		// login to ftp connection
		$isSuccess = $isSuccess && $this->login();
		// check to see if ftp connect is in passive mode
		$isSuccess = $isSuccess && $this->isPassive();
		// Transfer file
		$stream = fopen('data://text/plan,'.$string, 'r+');
		$isSuccess = $isSuccess && $this->transferStream($stream, $remoteFile);
		// close ftp connection
		$this->close();
		fclose($stream);
		return $isSuccess;
	}

	public function getString($remoteFile)
	{
		$remotePath = $this->normalPaths(
			$this->getConfig()->getRemotePath(),
			$remoteFile
		);
		// connect to ftp server
		$isSuccess = $this->connect();
		// login to ftp connection
		$isSuccess = $isSuccess && $this->login();
		// check to see if ftp connect is in passive mode
		$isSuccess = $isSuccess && $this->isPassive();
		// Transfer file
		$stream = fopen('data://text/plan,', 'r+');
		$isSuccess = $isSuccess && $this->retrieveStream($stream, $remoteFile);
		// close ftp connection
		$this->close();
		fclose($stream);
		return stream_get_contents($stream);
	}

	public function setHost($host='')
	{
		$this->getConfig()->setHost($host);
		return $this;
	}

	public function setPort($port='21')
	{
		$this->getConfig()->setPort($port);
		return $this;
	}

	public function setUsername($username='')
	{
		$this->getConfig()->setUsername($username);
		return $this;
	}

	public function setPassword($password='')
	{
		$this->getConfig()->setPassword($password);
		return $this;
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
				Mage::throwException("Failed to connect to 'ftp://".$this->getConfig()->getHost()."'.");
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
		if (!$this->_auth = ftp_login($this->_conn, $config->getUsername(), $config->getPassword())){
			try{
				Mage::throwException("Failed to authenticate to 'ftp://".$this->getConfig()->getHost()."@".$this->getConfig()->getHost()."'.");
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
				Mage::throwException("Failed to switch to passive mode on 'ftp://".$this->getConfig()->getHost()."'.");
			} catch (Exception $e) {
				$success = false;
				Mage::logException($e);
			}
		}
		return $success;
	}


	/**
	 * Transfer data from current local server to destination remote server.
	 *
	 * @param resource $stream
	 * @param String remoteFile
	 */
	public function transfer($stream, $remoteFile)
	{
		$success = true;
		Mage::log("Connected to ftp://".$this->getConfig()->getUsername()."@".$this->getConfig()->getHost()."");
		if (!$up = ftp_fput($this->_conn, $remoteFile, $stream, FTP_BINARY)) {
			try{
				Mage::throwException("Failed to upload '$localFile' to 'ftp://".$this->getConfig()->getHost()."'.");
			} catch (Exception $e) {
				$success = false;
				Mage::logException($e);
			}
		}else{
			Mage::log("Uploaded '$localFile' to 'ftp://".$this->getConfig()->getHost()."'.");
		}

		return $success;
	}

	/**
	 * Transfer data from destination remote server to current local server.
	 *
	 * @param resource $stream
	 * @param String remoteFile
	 */
	public function retrieve($stream, $remoteFile)
	{
		$success = true;
		Mage::log("Connected to ftp://".$this->getConfig()->getUsername()."@".$this->getConfig()->getHost()."");
		$up = ftp_fget($this->_conn, $localFile, $stream, FTP_BINARY);
		if (!$up) {
			try{
				Mage::throwException("Failed to download 'ftp://".$this->getConfig()->getHost()."/$remoteFile' to '$localFile'.");
			} catch (Exception $e) {
				$success = false;
				Mage::logException($e);
			}
		}else{
			Mage::log("Downloaded 'ftp://".$this->getConfig()->getHost()."/$remoteFile' to '$localFile'.");
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
