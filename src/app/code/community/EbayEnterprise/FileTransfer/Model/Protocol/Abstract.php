<?php
/**
 * @method array getConfig()
 * @method EbayEnterprise_ActiveConfig_Model_Config_Abstract getConfigModel()
 * @method string getString(string $remoteFile, string $data)
 */
abstract class EbayEnterprise_FileTransfer_Model_Protocol_Abstract extends Varien_Object
{
	/**
	 * setup a generic config object to be used when reading/generating config
	 * fields.
	 */
	protected function _construct()
	{
		$this->setConfigModel(
			Mage::getModel('filetransfer/protocol_config', $this->getConfig())
		);
		$this->setCode($this->getConfigModel()->getProtocolCode());
	}

	// cache of available protocols.
	private static $_protocolCodes = array();

	/**
	 * Get the specified file from the remote and put in at the specified local
	 * file path.
	 * @param string $remoteFile
	 * @param string $localFile
	 * @return boolean true if transfer was successful, false otherwise
	 * */
	abstract public function getFile($remoteFile, $localFile);
	/**
	 * Get all files in the remote directory matching the pattern and put them
	 * in the local directory The array returned will have key/value pairs of
	 * local and remote paths for each file retrieved.
	 * @param  string $remoteDirectory
	 * @param  string $localDirectory
	 * @param  string $pattern
	 * @return array Array of 'local' & 'remote' pairs for each file retrieved, e.g. array(array('local' => 'local/path', 'remote' => 'remote/path'), ...)
	 */
	abstract public function getAllFiles($remoteDirectory, $localDirectory, $pattern='*');
	/**
	 * Send file at the local file path to the remote at the remote file path.
	 * @param string $remoteFile
	 * @param string $localFile
	 * @return boolean true if transfer was successful, false otherwise
	 * */
	abstract public function sendFile($remoteFile, $localFile);
	/**
	 * Send all files in the local directory matching the pattern to the remote
	 * directory. The array returned will have key/value pairs of local and remote
	 * path for each file sent.
	 * @param  string $remoteDirectory
	 * @param  string $localDirectory
	 * @param  string $pattern
	 * @return array Array of 'local' & 'remote' pairs for each file sent, e.g. array(array('local' => 'local/path', 'remote' => 'remote/path'), ...)
	 */
	abstract public function sendAllFiles($remoteDirectory, $localDirectory, $pattern='*');
	/**
	 * Delete the file from the remote host.
	 * @param string $remoteFile
	 * @return boolean true of delete was successful, false otherwise
	 */
	abstract public function deleteFile($remoteFile);
	/**
	 * Join the directories in a canonical, platform-agnostic way.
	 * @param string,... $_ Variable number of strings to join to make the path
	 * @return string the joined path
	 */
	public static function normalPaths()
	{
		$paths = implode(DS, func_get_args());
		// Retain a single leading slash; otherwise remove all leading, trailing
		// and duplicate slashes.
		return ((substr($paths, 0, 1) === DS) ? DS : '') .
			implode(DS, array_filter(explode(DS, $paths)));
	}

	/**
	 * Scans the Protocol/Types directory for php files and uses their
	 * lowercased basename to get a list of protocol codes.
	 * @return array string protocol codes
	 * */
	public static function getCodes()
	{
		// if we've already discovered all of the protocols return the cached
		// list.
		if (!empty(self::$_protocolCodes)) {
			return self::$_protocolCodes;
		}
		$path = self::normalPaths(dirname(__FILE__), 'Types');
		$items = scandir($path);
		foreach ($items as $entry) {
			$entry = strtolower($entry);
			if (substr($entry, -4) === '.php') {
				self::$_protocolCodes[] = substr($entry, 0, -4);
			}
		}
		return self::$_protocolCodes;
	}

	/**
	 * create a data uri using the specified string as the data.
	 * @param  string $data
	 * @return string
	 */
	public static function getDataUriFromString($data='')
	{
		return 'data:text/plain,' . $data;
	}
	/**
	 * Set the host on the config model.
	 * @param string $host
	 * @return self
	 */
	public function setHost($host='')
	{
		$this->getConfigModel()->setHost($host);
		return $this;
	}
	/**
	 * Set the port on the config model.
	 * @param string $port
	 * @return self
	 */
	public function setPort($port=null)
	{
		$this->getConfigModel()->setPort($port);
		return $this;
	}
	/**
	 * Set the username on the config model
	 * @param string $username
	 * @return self
	 */
	public function setUsername($username='')
	{
		$this->getConfigModel()->setUsername($username);
		return $this;
	}
	/**
	 * Set the password on the config model
	 * @param string $password
	 * @return self
	 */
	public function setPassword($password='')
	{
		$this->getConfigModel()->setPassword($password);
		return $this;
	}
}
