<?php

class TrueAction_FileTransfer_Model_Key_Maker
	extends Varien_Object
{

	/**
	 * Varien_Object constructor - set up FsTool and base dirs
	 */
	protected function _construct()
	{
		if (!$this->hasFsTool()) {
			$this->setFsTool(new Varien_Io_File());
		}
		if (!$this->hasBaseDir()) {
			$this->setBaseDir(
				$this->getFsTool()->getCleanPath(Mage::getBaseDir('tmp') . DS . uniqid() . DS)
			);
		}
	}

	/**
	 * Clean up - ensure any created key files are destroyed.
	 */
	public function __destruct()
	{
		$this->destroyKeys();
	}

	/**
	 * If the base dir for the keys does not exist, create it.
	 * @return  boolean If the file exists.
	 */
	protected function _setUpKeyDir()
	{
		return $this->getFsTool()->checkAndCreateFolder($this->getBaseDir(), 0644);
	}

	/**
	 * Path to the public key. If a public key file name hasn't been created yet, generate a new one.
	 * @return string
	 */
	public function getPublicKeyPath()
	{
		$this->_pubKeyName = $this->_pubKeyName ?: uniqid();
		return $this->getBaseDir() . $this->_pubKeyName;
	}

	/**
	 * Path to the private key. If a private key file name hasn't been created yet, generate a new one.
	 * @return string
	 */
	public function getPrivateKeyPath()
	{
		$this->_privKeyName = $this->_privKeyName ?: uniqid();
		return $this->getBaseDir() . $this->_privKeyName;
	}

	/**
	 * Create a key with the given string as contents
	 * @param  string $keyContents contents of the key file
	 * @return boolean             successfully created keys
	 */
	public function createKeyFiles($pubKey, $privKey)
	{
		$this->_setUpKeyDir();
		// this is required by Varien_File_Io - sets up the _iw property,
		// without which the write calls error out. >:|
		$this->getFsTool()->open(array('path' => $this->getBaseDir()));
		$pubCreated = $this->getFsTool()->write($this->getPublicKeyPath(), $pubKey, 0644);
		$privCreated = $this->getFsTool()->write($this->getPrivateKeyPath(), $privKey, 0600);
		$msg = sprintf(
			'[ %s ] Generated files successfully: pub - %d, priv - %d', __CLASS__,
			$pubCreated, $privCreated
		);
		Mage::log($msg, Zend_Log::DEBUG);
		return $pubCreated && $privCreated;
	}

	/**
	 * Destory/delete the given key file
	 * @param  sting    $keyLocation path to the file to delete
	 * @return boolean
	 */
	public function destroyKeys()
	{
		$results = true;
		if ($this->getFsTool()->fileExists($this->getBaseDir(), false)) {
			$this->getFsTool()->cd($this->getBaseDir());
			foreach ($this->getFsTool()->ls(Varien_Io_File::GREP_FILES) as $keyFile) {
				$results = $results && $this->getFsTool()->rm($keyFile['text']);
			}
			$results = $results && $this->getFsTool()->rmdir($this->getBaseDir());
		}
		$msg = sprintf('[ %s ] Files deleted successfully: %d', __CLASS__, $results);
		Mage::log($msg, Zend_Log::DEBUG);
		return $results;
	}

}
