<?php
/**
 * Provide sftp commands with prebuilt configuration.
 * @method setCon Set/replace the Net_SFTP connection object.
 * @method getCon Get the Net_SFTP connection object.
 * @see phpseclib/Net/SFTP.php
 * @link http://phpseclib.sourceforge.net/sftp/intro.html
 */

// Include phpseclib in PHP's include path - required for all of the imports done within phpseclib
set_include_path(get_include_path().PS.Mage::getBaseDir('lib').DS.'phpseclib');

class TrueAction_FileTransfer_Model_Protocol_Types_Sftp extends TrueAction_FileTransfer_Model_Protocol_Abstract
{
	const NAME = 'SSH File Transfer Protocol';
	protected function _construct()
	{
		$this->setName(self::NAME);
		$this->setConfigModel(Mage::getModel('filetransfer/protocol_types_sftp_config', $this->getConfig()));
		$this->_initCon()->_login();
		Mage::log(sprintf('[%s] Created and authed new %s model', __CLASS__, $this->getName()));
	}
	/**
	 * Log a put.
	 * @param string $src
	 * @param string $dst
	 * @return self
	 * @codeCoverageIgnore
	 */
	protected function _logPut($src, $dst)
	{
		Mage::log(sprintf('[ %s ] Transferring %s to %s/%s.', __CLASS__, $src, $this->getConfigModel()->getUrl(), $dst), Zend_Log::DEBUG);
		return $this;
	}
	/**
	 * Log a get.
	 * @param string $src
	 * @param string $dst
	 * @return self
	 * @codeCoverageIgnore
	 */
	protected function _logGet($src, $dst)
	{
		Mage::log(sprintf('[ %s ] Transferring %s/%s to %s.', __CLASS__, $this->getConfigModel()->getUrl(), $src, $dst), Zend_Log::DEBUG);
		return $this;
	}
	/**
	 * Delete a file from the remote server.
	 * @param string $tgt The remote file to be deleted
	 * @return bool
	 * @codeCoverageIgnore
	 */
	protected function _logDel($tgt)
	{
		Mage::log(sprintf('[ %s ] Deleting remote file %s/%s.', __CLASS__, $this->getConfigModel()->getUrl(), $tgt), Zend_Log::DEBUG);
		return $this;
	}
	/**
	 * Put a file on the remote server.
	 * @param string $localFile path to a local file
	 * @param string $remoteFile path to a remote file relative to the configured root remote path
	 * @return bool true if transfer was successful
	 */
	public function sendFile($localFile, $remoteFile)
	{
		$remotePath = $this->_getRemotePath($remoteFile);
		$this->_logPut($localFile, $remotePath);
		return $this->getCon()->put($remotePath, $localFile, NET_SFTP_LOCAL_FILE);
	}
	/**
	 * Fetch a file from the remote server.
	 * @param string $localFile path where the local file should go
	 * @param string $remoteFile path to the remote file relative to the configured root remote path
	 * @return bool true if transfer was successful
	 */
	public function getFile($localFile, $remoteFile)
	{
		$remotePath = $this->_getRemotePath($remoteFile);
		$this->_logGet($remotePath, $localFile);
		return $this->getCon()->get($remotePath, $localFile);
	}
	/**
	 * Unlink the remote file via SFTP.
	 * @param  string $remoteFile File to be unlinked on the remote.
	 * @return boolean true if remote deletion was successful
	 */
	public function deleteFile($remoteFile)
	{
		$remotePath = $this->_getRemotePath($remoteFile);
		$this->_logDel($remotePath);
		return $this->getCon()->delete($remotePath); // doesn't delete directories
	}
	/**
	 * List all the files in the given remote directory whose names match the given pattern.
	 * @param string $remPath Target directory on remote
	 * @param string $pat Pattern to match (default '*' matches all non-dotfiles)
	 * @return bool|string[] array of absolute path names to each matching file or false if SFTP call fail
	 */
	public function listFilesMatchingPat($remPath, $pat='*')
	{
		$remPath = $this->_getRemotePath($remPath);
		$con = $this->getCon();

		$names = array();
		$remoteList = $con->rawlist($remPath);

		// when rawlist fails, this method should match the failure
		if ($remoteList === false) {
			return false;
		}

		foreach ($remoteList as $name => $stat) {
			if (fnmatch($pat, $name)) {
				$names[] = "$remPath/$name";
			}
		}
		return $names;
	}
	/**
	 * Retrieve all files in the given remote directory, optionally matching the given
	 * pattern and copy them to the provided local directory
	 * @param string $locPath Path to local target directory
	 * @param string $remPath Path to the source directory on the remote server
	 * @param string $pattern Glob pattern the files must match (default '*')
	 * @return boolean true if all transfers were successful or no files were found
	 */
	public function getAllFiles($locPath, $remPath, $pattern='*')
	{
		$success = true;
		$fileList = $this->listFilesMatchingPat($remPath, $pattern);

		if ($fileList === false) {
			return false;
		}

		foreach ($fileList as $remFile) {
			$locFile = $this->normalPaths($locPath, basename($remFile));
			$success = $success && $this->getFile($locFile, $remFile);
		}
		return $success;
	}
	/**
	 * Write a string to a specified remote file
	 * @param  string  $string     String to write
	 * @param  string  $remoteFile File on the remote to write the string to
	 * @return boolean             True if successful, false if failed
	 */
	public function sendString($string, $remoteFile)
	{
		$remotePath = $this->_getRemotePath($remoteFile);
		$this->_logPut('string', $remotePath); // Don't log actual string.
		return $this->getCon()->put($remotePath, $string);
	}
	/**
	 * Get the contents of a remote file
	 * @param  string $remoteFile File on the remote to get the contents of
	 * @return string|bool        Contents of file if successful, false if failed
	 */
	public function getString($remoteFile)
	{
		$remotePath = $this->_getRemotePath($remoteFile);
		$this->_logGet('string', $remotePath); // Don't log actual string.
		return $this->getCon()->get($remotePath);
	}
	public function setPort($port=22)
	{
		$this->getConfigModel()->setPort($port);
		return $this;
	}
	/**
	 * Set up the Net_SFTP object.
	 * Allow, but don't require, dependency injection of the Net_SFTP instance.
	 * @return self
	 */
	protected function _initCon()
	{
		if (!$this->hasCon()) {
			$cfg = $this->getConfigModel();
			$this->setCon(new Net_SFTP($cfg->getHost(), $cfg->getPort()));
		}
		return $this;
	}
	/**
	 * Login to server
	 * @return self
	 */
	protected function _login()
	{
		$cfg = $this->getConfigModel();
		if ($cfg->getAuthType() === 'pub_key') {
			return $this->_loginKey();
		} else {
			return $this->_loginPass();
		}
	}
	/**
	 * Log in to sftp using a password.
	 * @return self
	 */
	protected function _loginPass()
	{
		$sftp = $this->getCon();
		$cfg = $this->getConfigModel();
		if (!$sftp->login($cfg->getUsername(), $cfg->getPassword())) {
			throw new TrueAction_FileTransfer_Exception_Authentication();
		}
		return $this;
	}
	/**
	 * Log in to sftp using a key.
	 * @return Net_SFTP
	 */
	protected function _loginKey()
	{
		$sftp = $this->getCon();
		if (!$sftp->login($this->getConfigModel()->getUsername(), $this->_getPrivateKey())) {
			throw new TrueAction_FileTransfer_Exception_Authentication();
		}
		return $this;
	}
	/**
	 * Create a Crypt RSA container with configured private key loaded
	 * Allow, but don't require, dependency injection of the Crypt_RSA instance
	 * @return  Crypt_RSA Container with a loaded private key
	 */
	protected function _getPrivateKey()
	{
		$rsa = $this->getRsa() ?: new Crypt_RSA();
		$rsa->loadKey($this->getConfigModel()->getPrivateKey());
		return $rsa;
	}
	/**
	 * @param string $relPath the unnormalized relative remote path
	 * @return string the normalized absolute remote path
	 */
	protected function _getRemotePath($relPath)
	{
		return $this->normalPaths('/', $this->getConfigModel()->getRemotePath(), $relPath);
	}
}
