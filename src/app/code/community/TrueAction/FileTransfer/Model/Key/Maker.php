<?php
class TrueAction_FileTransfer_Model_Key_Maker extends Varien_Object
{
	private $_publicKeyPath;
	private $_privateKeyPath;

	/**
	 * Varien_Object constructor - set up FsTool and base dirs
	 */
	protected function _construct()
	{
		if (!$this->hasFsTool()) {
			$this->setFsTool(new Varien_Io_File());
		}
		if (!$this->hasBaseDir()) {
			$this->setBaseDir(Mage::getBaseDir('tmp'));
		}
		if (!$this->hasTmpPrefix()) {
			// Advise callers to set a prefix value for the tempnam() builtin.
			// It makes debugging easier, at least.
			Mage::log(
				sprintf('[ %s ] prefix not set for tempfile. Using classname as generic default.', __CLASS__),
				Zend_Log::WARN
			);
			$this->setTmpPrefix(__CLASS__);
		}
		// Don't make it possible for calling functions to modify the public
		// and private key paths. They should always be created by a secure
		// temporary file creation tool.
		$this->_publicKeyPath = tempnam($this->getBaseDir(), $this->getTmpPrefix());
		$this->_privateKeyPath = tempnam($this->getBaseDir(), $this->getTmpPrefix());
	}

	/**
	 * Get the public key path
	 * @return string file path to the public key
	 */
	public function getPublicKeyPath()
	{
		return $this->_publicKeyPath;
	}

	/**
	 * Get the private key path
	 * @return string file path to the private key
	 */
	public function getPrivateKeyPath()
	{
		return $this->_privateKeyPath;
	}

	/**
	 * Clean up - ensure any created key files are destroyed.
	 */
	public function __destruct()
	{
		$this->_destroyKeys();
	}

	/**
	 * Create a key with the given string as contents
	 * @param string $pubKey contents to put in the public key file
	 * @param string $privKey contents to put in the private key file
	 * @return boolean successfully created keys
	 */
	public function createKeyFiles($pubKey, $privKey)
	{
		// this is required by Varien_File_Io - sets up the _iw property,
		// without which the write calls error out. >:|
		$this->getFsTool()->open(array('path' => $this->getBaseDir()));
		$pubCreated = $this->getFsTool()->write($this->getPublicKeyPath(), $pubKey, 0600);
		$privCreated = $this->getFsTool()->write($this->getPrivateKeyPath(), $privKey, 0600);
		$msg = sprintf(
			'[ %s ] Generated files successfully: pub - %d, priv - %d', __CLASS__,
			$pubCreated, $privCreated
		);
		Mage::log($msg, Zend_Log::DEBUG);
		return $pubCreated && $privCreated;
	}

	/**
	 * Destroy/delete the key files
	 * @return null
	 */
	private function _destroyKeys()
	{
		$fs = $this->getFsTool();
		$pub = $this->getPublicKeyPath();
		$priv = $this->getPrivateKeyPath();
		if ($fs->fileExists($pub)) {
			$fs->rm($pub);
			Mage::log(
				sprintf('[ %s ] Deleted public key "%s".', __CLASS__, $pub),
				Zend_Log::DEBUG
			);
		}
		if ($fs->fileExists($priv)) {
			$fs->rm($priv);
			Mage::log(
				sprintf('[ %s ] Deleted private key "%s".', __CLASS__, $priv),
				Zend_Log::DEBUG
			);
		}
	}
}
