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
		if( !$this->hasAdapter() ) {
			$this->setAdapter(Mage::getModel('filetransfer/adapter_sftp'));
		}
		$config = new TrueAction_FileTransfer_Model_Protocol_Types_Sftp_Config(
			$this->getConfig()
		);
		$this->setConfig($config);
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
		$stream = $this->getAdapter()->fopen($localFile, 'w+');
		$isSuccess = $isSuccess && $this->retrieve($stream, $remotePath);
		$this->getAdapter()->fclose($stream);
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
		$stream = $this->getAdapter()->fopen($this->getDataUriFromString($string), 'r+');
		$isSuccess = $isSuccess && $this->transfer($stream, $remotePath);
		$this->getAdapter()->fclose($stream);
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
		$stream = $this->getAdapter()->fopen($this->getDataUriFromString(), 'r+');
		$isSuccess = $isSuccess && $this->retrieve($stream, $remotePath);
		$output = $this->getAdapter()->streamGetContents($stream, -1, 0);
		$this->getAdapter()->fclose($stream);
		return $output;
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
		$this->_conn = $this->getAdapter()->ssh2Connect($config->getHost(), $config->getPort());
		if (!$this->_conn) {
			try{
				Mage::throwException(
					"Failed to connect to 'sftp://" . $this->getConfig()->getHost() . "'."
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
		if (!$this->_sftp = $this->getAdapter()->ssh2Sftp($this->_conn)) {
			try{
				Mage::throwException(
					"Failed to start SFTP subsystem on 'sftp://" . $this->getConfig()->getHost() . "'."
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
		$this->_authenticate();
		if (!$this->_auth){
			try{
				Mage::throwException(
					"Failed to authenticate to 'sftp://" . $config->getHost() . '@' . $config->getHost() . "'."
				);
			} catch (Exception $e) {
				$success = false;
				Mage::logException($e);
			}
		}
		return $success;
	}

	/**
	 * authenticate with either an ssh key or a password
	 */
	protected function _authenticate()
	{
		$config = $this->getConfig();
		if ($config->getAuthType() === 'pub_key') {
			$keyMaker = Mage::getModel('filetransfer/key_maker');
			$keyMaker->createKeyFiles($config->getPublicKey(), $config->getPrivateKey());
			$this->_auth = $this->getAdapter()->ssh2AuthPubkeyFile(
				$this->_conn,
				$config->getUsername(),
				$keyMaker->getPublicKeyPath(),
				$keyMaker->getPrivateKeyPath()
			);
		} else {
			$this->_auth = $this->getAdapter()->ssh2AuthPassword($this->_conn, $config->getUsername(), $config->getPassword());
		}
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
		Mage::log('Connected to sftp://' . $this->getConfig()->getUsername() . '@' . $this->getConfig()->getHost());
		$remoteStream = $this->getAdapter()->fopen("ssh2.sftp://{$this->_sftp}{$remoteFile}", 'w');
		if (!$remoteStream) {
			try{
				Mage::throwException("Failed to open $remoteFile on 'sftp://" . $this->getConfig()->getHost() . "'.");
			} catch (Exception $e) {
				$success = false;
				Mage::logException($e);
			}
		}else{
			if (false === $this->getAdapter()->fwrite($remoteStream, $this->getAdapter()->streamGetContents($stream))) {
				$success = false;
			}
			Mage::log("Uploaded data to 'sftp://" . $this->getConfig()->getHost() . "'.");
		}
		$this->getAdapter()->fclose($remoteStream);
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
		Mage::log('Connected to sftp://' . $this->getConfig()->getUsername() . '@' . $this->getConfig()->getHost());
		$remoteStream = $this->getAdapter()->fopen("ssh2.sftp://{$this->_sftp}{$remoteFile}", 'r');
		if (!$remoteStream) {
			try{
				Mage::throwException("Failed to open $remoteFile on 'sftp://" . $this->getConfig()->getHost() . "'.");
			} catch (Exception $e) {
				$success = false;
				Mage::logException($e);
			}
		}else{
			if (false === $this->getAdapter()->fwrite($stream, $this->getAdapter()->streamGetContents($remoteStream))) {
				$success = false;
			}
			Mage::log("Downloaded data from 'sftp://" . $this->getConfig()->getHost() . "'.");
		}
		$this->getAdapter()->fclose($remoteStream);
		return $success;
	}
}
