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

class EbayEnterprise_FileTransfer_Model_Protocol_Types_Sftp extends EbayEnterprise_FileTransfer_Model_Protocol_Abstract
{
	const NAME = 'SSH File Transfer Protocol';
	protected function _construct()
	{
		$this->setName(self::NAME);
		$this->setConfigModel(Mage::getModel('filetransfer/protocol_types_sftp_config', $this->getConfig()));
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
	 * @see parent::getFile()
	 */
	public function getFile($localFile, $remoteFile)
	{
		$remotePath = $this->_getRemotePath($remoteFile);
		$this->_logGet($remotePath, $localFile);
		return $this->getCon()->get($remotePath, $localFile);
	}
	/**
	 * @see parent::getAllFiles()
	 */
	public function getAllFiles($localDirectory, $remoteDirectory, $pattern='*')
	{
		$fileList = $this->listFilesMatchingPattern($remoteDirectory, $pattern) ?: array();
		$receivedFiles = array();
		foreach ($fileList as $remFile) {
			$locFile = $this->normalPaths($localDirectory, basename($remFile));
			if ($this->getFile($locFile, $remFile)) {
				$receivedFiles[] = array('local' => $locFile, 'remote' => $remFile);
			}
		}
		return $receivedFiles;
	}
	/**
	 * @see parent::sendFile()
	 */
	public function sendFile($localFile, $remoteFile)
	{
		$remotePath = $this->_getRemotePath($remoteFile);
		$this->_logPut($localFile, $remotePath);
		return $this->getCon()->put($remotePath, $localFile, NET_SFTP_LOCAL_FILE);
	}
	/**
	 * @see parent::sendAllFiles()
	 */
	public function sendAllFiles($localDirectory, $remoteDirectory, $pattern='*')
	{
		$fileList = Mage::helper('filetransfer/file')->listFilesInDirectory($localDirectory, $pattern);
		$sentFiles = array();
		foreach ($fileList as $locPath => $locFileInfo) {
			// can't send anything that isn't a file
			if (!$locFileInfo->isFile()) {
				continue;
			}
			$remFile = $this->normalPaths($remoteDirectory, basename($locPath));
			if ($this->sendFile($locPath, $remFile)) {
				$sentFiles[] = array('local' => $locPath, 'remote' => $remFile);
			}
		}
		return $sentFiles;
	}
	/**
	 * @see parent::deleteFile()
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
	public function listFilesMatchingPattern($remPath, $pat='*')
	{
		$remPath = $this->_getRemotePath($remPath);
		$con = $this->getCon();

		$names = array();
		$remoteList = $con->rawlist($remPath);

		// when rawlist fails, this method should match the failure
		if ($remoteList === false) {
			return false;
		}

		foreach (array_keys($remoteList) as $name) {
			if (fnmatch($pat, $name)) {
				$names[] = "$remPath/$name";
			}
		}
		return $names;
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
	 * When getting the connection object, make sure to make the connection
	 * and log it in.
	 * @return Net_SFTP connection object
	 */
	public function getCon()
	{
		return $this->connect()->login()->getData('con');
	}
	/**
	 * Set up the Net_SFTP object.
	 * Allow, but don't require, dependency injection of the Net_SFTP instance.
	 * @return self
	 */
	public function connect()
	{
		if (!$this->hasCon()) {
			$cfg = $this->getConfigModel();
			$this->setCon(new Net_SFTP($cfg->getHost(), $cfg->getPort()));
		}
		return $this;
	}
	/**
	 * Check if the connection object (Net_SFTP) has been logged in
	 * @return boolean true if logged in, false if not
	 */
	public function isLoggedIn()
	{
		// use getData method to get the con object to get around the actual getCon method
		// which would result in an infinite loop (getCon --> login --> isLoggedIn --> getCon --> ...)
		return $this->hasCon() && $this->getData('con')->bitmap & NET_SSH2_MASK_LOGIN;
	}
	/**
	 * Login to server
	 * @return self
	 */
	public function login()
	{
		if (!$this->isLoggedIn()) {
			$cfg = $this->getConfigModel();
			if ($cfg->getAuthType() === 'pub_key') {
				return $this->_loginKey();
			} else {
				return $this->_loginPass();
			}
		}
		return $this;
	}
	/**
	 * Log in to sftp using a password.
	 * @return self
	 */
	protected function _loginPass()
	{
		// use getData to avoid actual getCon method which which would result in loop
		$sftp = $this->getData('con');
		$cfg = $this->getConfigModel();
		if (!$sftp->login($cfg->getUsername(), $cfg->getPassword())) {
			throw new EbayEnterprise_FileTransfer_Exception_Authentication(
				sprintf('Could not authenticate to %s', $cfg->getUrl())
			);
		}
		return $this;
	}
	/**
	 * Log in to sftp using a key.
	 * @return Net_SFTP
	 */
	protected function _loginKey()
	{
		// use getData to avoid actual getCon method which which would result in loop
		$sftp = $this->getData('con');
		$cfg = $this->getConfigModel();
		if (!$sftp->login($cfg->getUsername(), $this->_getPrivateKey())) {
			throw new EbayEnterprise_FileTransfer_Exception_Authentication(
				sprintf('Could not authenticate to %s', $cfg->getUrl())
			);
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
