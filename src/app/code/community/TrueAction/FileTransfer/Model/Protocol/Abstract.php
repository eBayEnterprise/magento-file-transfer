<?php
abstract class TrueAction_FileTransfer_Model_Protocol_Abstract extends Mage_Core_Model_Abstract
{
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
	public function normalPaths()
	{
		$paths = implode(DS, func_get_args());
		// Retain a single leading slash; otherwise remove all leading, trailing
		// and duplicate slashes.
		return ((substr($paths, 0, 1) === DS) ? DS : '') .
			implode(DS, array_filter(explode(DS, $paths)));
	}
}
