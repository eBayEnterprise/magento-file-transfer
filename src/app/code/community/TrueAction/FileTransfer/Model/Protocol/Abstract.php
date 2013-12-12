<?php
/**
 * @method array getConfig()
 * @method TrueAction_ActiveConfig_Model_Config_Abstract getConfigModel()
 * @method string getString(string $remoteFile, string $data)
 */
abstract class TrueAction_FileTransfer_Model_Protocol_Abstract extends Mage_Core_Model_Abstract
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
	 * @param string $remoteFile
	 * @param string $localFile
	 * */
	abstract public function sendFile($remoteFile, $localFile);

	/**
	 * @param string $remoteFile
	 * @param string $localFile
	 * */
	abstract public function getFile($remoteFile, $localFile);

	/**
	 * Join the directories in a canonical, platform-agnostic way.
	 * @param ? Some number of strings
	 * @return The joined path (string)
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
	 * scans the Protocol/Types directory for php files and uses their
	 * lowercased basename to get a list of protocol codes.
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

	public function setHost($host='')
	{
		$this->getConfigModel()->setHost($host);
		return $this;
	}

	public function setPort($port=null)
	{
		$this->getConfigModel()->setPort($port);
		return $this;
	}

	public function setUsername($username='')
	{
		$this->getConfigModel()->setUsername($username);
		return $this;
	}

	public function setPassword($password='')
	{
		$this->getConfigModel()->setPassword($password);
		return $this;
	}

	/**
	 * throw a connection exception.
	 * if $useDefaultFormat is false, the $message will be used as the exception
	 * message without alteration.
	 * otherwise the following message format will be used.
	 * sftp://host[/remote_path] connection error[: $message]
	 * @param  string  $message
	 * @param  boolean $useDefaultFormat
	 * @throws TrueAction_FileTransfer_Exception_Connection
	 */
	protected function _connectionError($message='', $useDefaultFormat=true)
	{
		if ($useDefaultFormat) {
			$message = sprintf(
				'%s connection error%s',
				$this->getConfigModel()->getUrl(),
				($message) ? ': ' . $message : ''
			);
		}
		throw new TrueAction_FileTransfer_Exception_Connection($message);
	}

	/**
	 * throw an authentication exception.
	 * if $useDefaultFormat is false, the $message will be used as the exception
	 * message without alteration.
	 * otherwise the following message format will be used.
	 * sftp://host[/remote_path] authentication error[: $message]
	 * @param  string  $message
	 * @param  boolean $useDefaultFormat
	 * @throws TrueAction_FileTransfer_Exception_Connection
	 */
	protected function _authenticationError($message='', $useDefaultFormat=true)
	{
		if ($useDefaultFormat) {
			$message = sprintf(
				'%s authentication error%s',
				$this->getConfigModel()->getUrl(),
				($message) ? ': ' . $message : ''
			);
		}
		throw new TrueAction_FileTransfer_Exception_Authentication($message);
	}

	/**
	 * throw a transfer exception.
	 * if $useDefaultFormat is false, the $message will be used as the exception
	 * message without alteration.
	 * otherwise the following message format will be used.
	 * sftp://host[/remote_path] transfer error[: $message]
	 * @param  string  $message
	 * @param  boolean $useDefaultFormat
	 * @throws TrueAction_FileTransfer_Exception_Connection
	 */
	protected function _transferError($message='', $useDefaultFormat=true)
	{
		if ($useDefaultFormat) {
			$message = sprintf(
				'%s transfer error%s',
				$this->getConfigModel()->getUrl(),
				($message) ? ': ' . $message : ''
			);
		}
		throw new TrueAction_FileTransfer_Exception_Transfer($message);
	}
}
