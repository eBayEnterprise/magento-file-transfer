<?php
class TrueAction_FileTransfer_Model_Adminhtml_System_Config_Backend_Encrypted_Keyfile extends Mage_Adminhtml_Model_System_Config_Backend_File
{
	/**
	 * saves the content of the private key file into the db
	 * @return self
	 */
	protected function _beforeSave()
	{
		// read the key from the uploaded temp file
		$key = $this->_readKey();
		// encrypt the key and set it to be saved
		$encryptedKey = Mage::helper('core')->encrypt($key);
		$this->setValue($encryptedKey);
		return $this;
	}

	protected function _afterLoad()
	{
		return $this->setValue(
			Mage::helper('core')->decrypt($this->getValue())
		);
	}

	/**
	 * read the uploaded file and return the contents
	 * @return string contents of the uploaded key file
	 */
	protected function _readKey()
	{
		$key = '';
		$tempName = $this->_getTempName();
		if ($tempName) {
			$key = $this->_fileGetContents($tempName);
		}
		return $key;
	}

	/**
	 * get the temp name of the uploaded file
	 * @return string name of the temporary file
	 */
	protected function _getTempName()
	{
		return $_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'];
	}

	/**
	 * get the contents of a file
	 * @param  string $path path to the file to read
	 * @return string       file contents
	 * @codeCoverageIgnore
	 */
	protected function _fileGetContents($path)
	{
		return file_get_contents($path);
	}
}
