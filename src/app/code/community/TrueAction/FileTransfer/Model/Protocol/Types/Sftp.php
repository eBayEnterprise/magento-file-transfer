<?php
/*
TrueAction_FileTransfer_Model_Config_Ftp

concrete configuration generator for the ftp protocol.
 */
class TrueAction_FileTransfer_Model_Protocol_Types_Sftp extends TrueAction_FileTransfer_Model_Protocol_Abstract
{
	protected $_conn;
	protected $_auth;
	protected $_sftp;

	public function _construct()
	{
		$this->setName('SSH File Transfer Protocol');
	}

	public function sendFile($localFile, $remoteFile)
	{
		$remotePath = $this->normalPaths(
			'/', // all remote paths must start with a /
			$this->getConfig()->getRemotePath(),
			$remoteFile
		);
		$localPath = $localFile;
		// connect to sftp server
		$isSuccess = $this->connect();
		// login to sftp connection
		$isSuccess = $isSuccess && $this->login();
		// init sftp subsystem
		$isSuccess = $isSuccess && $this->initSftp();
		// Transfer file
		$stream = fopen($localFile, 'r');
		$isSuccess = $isSuccess && $this->transfer($stream, $remotePath);
		fclose($stream);
		return $isSuccess;
	}

	public function getFile($localFile, $remoteFile)
	{
		$remotePath = $this->normalPaths(
			'/', // all remote paths must start with a /
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
		// init sftp subsystem
		$isSuccess = $isSuccess && $this->initSftp();
		// Transfer file
		$stream = fopen($localFile, 'w+');
		$isSuccess = $isSuccess && $this->retrieve($stream, $remotePath);
		fclose($stream);
		return $isSuccess;
	}

	public function sendString($string, $remoteFile)
	{
		$remotePath = $this->normalPaths(
			'/', // all remote paths must start with a /
			$this->getConfig()->getRemotePath(),
			$remoteFile
		);
		// connect to ftp server
		$isSuccess = $this->connect();
		// login to ftp connection
		$isSuccess = $isSuccess && $this->login();
		// init sftp subsystem
		$isSuccess = $isSuccess && $this->initSftp();
		// Transfer file
		$stream = fopen($this->_getDataUriFromString($string), 'r+');
		$isSuccess = $isSuccess && $this->transfer($stream, $remotePath);
		fclose($stream);
		return $isSuccess;
	}

	public function getString($remoteFile)
	{
		$remotePath = $this->normalPaths(
			'/', // all remote paths must start with a /
			$this->getConfig()->getRemotePath(),
			$remoteFile
		);
		// connect to ftp server
		$isSuccess = $this->connect();
		// login to ftp connection
		$isSuccess = $isSuccess && $this->login();
		// init sftp subsystem
		$isSuccess = $isSuccess && $this->initSftp();
		// Transfer file
		$stream = fopen($this->_getDataUriFromString(), 'r+');
		$isSuccess = $isSuccess && $this->retrieve($stream, $remotePath);
		$output = stream_get_contents($stream, -1, 0);
		fclose($stream);
		return $output;
	}

	/**
	 * get the decrypted private key as a data URI
	 */
	public function getPrivateKey()
	{
		return $this->getDataUriFromString(
			Mage::helper('core')->decrypt($this->getData('private_key'))
		);
	}

	/**
	 * get the decrypted public key as a data URI
	 */
	public function getPublicKey()
	{
		return $this->getDataUriFromString(
			Mage::helper('core')->decrypt($this->getData('public_key'))
		);
	}

	/**
	 * set the decrypted private key as a data URI
	 */
	public function setPrivateKey($key)
	{
		$this->getConfig()->setPrivateKey(
			Mage::helper('core')->encrypt($this->getData('private_key'))
		);
		return $this;
	}

	/**
	 * set the decrypted public key as a data URI
	 */
	public function setPublicKey()
	{
		$this->getConfig()->setPrivateKey(
			Mage::helper('core')->encrypt($this->getData('public_key'))
		);
		return $this;
	}

	public function setPort($port='22')
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
		$this->_conn = ssh2_connect($config->getHost(), $config->getPort());
		if (!$this->_conn) {
			try{
				Mage::throwException(
					"Failed to connect to 'sftp://".$this->getConfig()->getHost()."'."
				);
			} catch (Exception $e) {
				$success = false;
				Mage::logException($e);
			}
		}
		return $success;
	}

	/**
	 * tell the server to start the sftp subsystem.
	 *
	 * @param host
	 * @param port
	 */
	public function initSftp()
	{
		$config = $this->getConfig();
		$success = true;
		if (!$this->_sftp = ssh2_sftp($this->_conn)) {
			try{
				Mage::throwException(
					"Failed to start SFTP subsystem on 'sftp://".$this->getConfig()->getHost()."'."
				);
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
		if (!$this->_auth = ssh2_auth_password($this->_conn, $config->getUsername(), $config->getPassword())){
			try{
				Mage::throwException(
					"Failed to authenticate to 'sftp://".$this->getConfig()->getHost()."@".$this->getConfig()->getHost()."'."
				);
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
		Mage::log("$remoteFile");
		Mage::log("Connected to sftp://".$this->getConfig()->getUsername()."@".$this->getConfig()->getHost()."");
		$remoteStream = fopen("ssh2.sftp://{$this->_sftp}{$remoteFile}", 'w');
		if (!$remoteStream) {
			try{
				Mage::throwException("Failed to open $remoteFile on 'sftp://".$this->getConfig()->getHost()."'.");
			} catch (Exception $e) {
				$success = false;
				Mage::logException($e);
			}
		}else{
			if (false === fwrite($remoteStream, stream_get_contents($stream))) {
				$success = false;
			}
			Mage::log("Uploaded data to 'sftp://".$this->getConfig()->getHost()."'.");
		}
		fclose($remoteStream);
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
		Mage::log("Connected to sftp://".$this->getConfig()->getUsername()."@".$this->getConfig()->getHost()."");
		$remoteStream = fopen("ssh2.sftp://{$this->_sftp}{$remoteFile}", 'r');
		if (!$remoteStream) {
			try{
				Mage::throwException("Failed to open $remoteFile on 'sftp://".$this->getConfig()->getHost()."'.");
			} catch (Exception $e) {
				$success = false;
				Mage::logException($e);
			}
		}else{
			if (false === fwrite($stream, stream_get_contents($remoteStream))) {
				$success = false;
			}
			Mage::log("Downloaded data from 'sftp://".$this->getConfig()->getHost()."'.");
		}
		fclose($remoteStream);
		return $success;
	}
}
