<?php
abstract class TrueAction_FileTransfer_Model_Protocol_Abstract extends Mage_Core_Model_Abstract
{
	// cache of available protocols.
	private static $_protocolCodes = array();

	/**
	 * Magic getter that returns the protocol's config
	 * @return TrueAction_ActiveConfig_Model_Config_Abstract
	 * */
	// public function getConfig();

	/**
	 * @param string $remoteFile
	 * @param string $localFile
	 * */
	abstract public function sendFile($remoteFile, $localFile);

	/**
	 * @param string $remoteFile
	 * @param string $data
	 * */
	// public function sendString($remoteFile, $data);

	/**
	 * @param string $remoteFile
	 * @param string $localFile
	 * */
	abstract public function getFile($remoteFile, $localFile);

	/**
	 * @param string $remoteFile
	 * @param string $data
	 * */
	// public function getString($remoteFile, $data);


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

	public function setHost($host='')
	{
		$this->getConfig()->setHost($host);
		return $this;
	}

	public function setPort($port=null)
	{
		$this->getConfig()->setPort($port);
		return $this;
	}

	public function setUsername($username='')
	{
		$this->getConfig()->setUsername($username);
		return $this;
	}

	public function setPassword($password='')
	{
		$this->getConfig()->setPassword($password);
		return $this;
	}
}
