<?php
class TrueAction_FileTransfer_Model_Adminhtml_System_Config_Backend_Encrypted_Keyfile extends Mage_Adminhtml_Model_System_Config_Backend_File
{
	/**
	 * saves the content of the private key file into the db
	 * @return self
	 */
	protected function _beforeSave()
	{
		// save the file name
		$this->setValue($this->_getOriginalFilename());
		// encrypt the key and set it to be saved
		Mage::getModel('adminhtml/system_config_backend_encrypted')->addData(array(
			'scope' => $this->getScope(),
			'scope_id' => $this->getScopeId(),
			'path' => $this->_getKeyFieldPath(),
			'value' => $this->_readKey(),
		))->save();
		// delete the file
		$this->_deleteUploadedFile();

		return $this;
	}

	/**
	 * @return string config path of the key field
	 */
	protected function _getKeyFieldPath()
	{
		return substr_replace($this->getPath(), 'prv_key', -strlen('key_file'));
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
	 * @return string original name of the file
	 * @codeCoverageIgnore
	 */
	protected function _getOriginalFilename()
	{
		return $_FILES['groups']['name'][$this->getGroupId()]['fields'][$this->getField()]['value'];
	}

	/**
	 * @return string name of the temporary file
	 * @codeCoverageIgnore
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

	/**
	 * ensure the uploaded key file is not readily accessible
	 * @return  self
	 */
	protected function _deleteUploadedFile()
	{
		@unlink($this->_getTempName());
		return $this;
	}
}
