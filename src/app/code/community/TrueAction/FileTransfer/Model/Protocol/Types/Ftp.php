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

	public function _construct()
	{
		$this->setName('File Transfer Protocol');
		$this->setCode('ftp');
	}

	public function sendFile($localFile, $remoteFile)
	{
		$remotePath = $this->normalPaths(
			$this->getConfig()->getRemotePath(),
			$remoteFile
		);
		$localPath = $localFile;
		// connect to ftp server
		$isSuccess = $this->connect();
		// login to ftp connection
		$isSuccess = $isSuccess && $this->login();
		// check to see if ftp connect is in passive mode
		$isSuccess = $isSuccess && $this->isPassive();
		// Transfer file
		$stream = fopen($localFile, 'r');
		$isSuccess = $isSuccess && $this->transfer($stream, $remotePath);
		// close ftp connection
		$this->close();
		fclose($stream);
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
		$stream = fopen($localFile, 'w+');
		$isSuccess = $isSuccess && $this->retrieve($stream, $remotePath);
		// close ftp connection
		$this->close();
		fclose($stream);
		return $isSuccess;
	}

	public function sendString($string, $remoteFile)
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
		$stream = fopen('data:text/plan,'.$string, 'r+');
		$isSuccess = $isSuccess && $this->transfer($stream, $remoteFile);
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
		$stream = fopen('data:text/plan,', 'r+');
		$isSuccess = $isSuccess && $this->retrieve($stream, $remoteFile);
		// close ftp connection
		$this->close();
		$output = stream_get_contents($stream, -1, 0);
		fclose($stream);
		return $output;
	}

	public function setPort($port='21')
	{
		$this->getConfig()->setPort($port);
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
		$this->_conn = ftp_connect($config->getHost(), $config->getPort());
		if (!$this->_conn){
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
				Mage::throwException("Failed to upload data to 'ftp://".$this->getConfig()->getHost()."'.");
			} catch (Exception $e) {
				$success = false;
				Mage::logException($e);
			}
		}else{
			Mage::log("Uploaded data to 'ftp://".$this->getConfig()->getHost()."'.");
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
		$up = ftp_fget($this->_conn, $stream, $remoteFile, FTP_BINARY);
		if (!$up) {
			try{
				Mage::throwException("Failed to download 'ftp://".$this->getConfig()->getHost()."/$remoteFile'.");
			} catch (Exception $e) {
				$success = false;
				Mage::logException($e);
			}
		}else{
			Mage::log("Downloaded 'ftp://".$this->getConfig()->getHost()."/$remoteFile'.");
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
