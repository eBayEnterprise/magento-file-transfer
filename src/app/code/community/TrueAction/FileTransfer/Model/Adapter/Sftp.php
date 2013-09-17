<?php
/**
 * Adapter for Sftp
 *
 * Basically just wraps php functions; HOWEVER in order to comply with coding standards, we camelCase
 * stream_get_contents and the ssh2_xxx functions, and then apply _underscore to them to make the
 * correct call. This is how we can conform to sniff rules and make this as simple as possible.
 */
class TrueAction_FileTransfer_Model_Adapter_Sftp extends Varien_Object
{
	/**
	 * Close a stream
	 *
	 * @see fclose
	 */
	public function fclose()
	{
		return call_user_func_array(__FUNCTION__, func_get_args());
	}

	/**
	 * Read from a stream
	 *
	 * @see fread
	 */
	public function fread()
	{
		return call_user_func_array(__FUNCTION__, func_get_args());
	}

	/**
	 * Open a stream
	 *
	 * @see fopen
	 */
	public function fopen()
	{
		return call_user_func_array(__FUNCTION__, func_get_args());
	}

	/**
	 * Write to a stream
	 *
	 * @see fwrite
	 */
	public function fwrite()
	{
		return call_user_func_array(__FUNCTION__, func_get_args());
	}

	/**
	 * Open a directory handle
	 *
	 * @see  opendir
	 */
	public function opendir()
	{
		return call_user_func_array(__FUNCTION__, func_get_args());
	}

	/**
	 * Close a directory handle
	 *
	 * @see  closedir
	 */
	public function closedir()
	{
		return call_user_func_array(__FUNCTION__, func_get_args());
	}

	/**
	 * Read entry from directory handle
	 *
	 * @see  readdir
	 */
	public function readdir()
	{
		return call_user_func_array(__FUNCTION__, func_get_args());
	}

	/**
	 * Check if the given file is a regular file
	 *
	 * @see  is_file
	 */
	public function isFile()
	{
		return call_user_func_array($this->_underscore(__FUNCTION__), func_get_args());
	}

	/**
	 * Get contents of a stream
	 *
	 * @see stream_get_contents
	 */
	public function streamGetContents()
	{
		return call_user_func_array($this->_underscore(__FUNCTION__), func_get_args());
	}

	/**
	 * Connect using ssh2
	 *
	 * @see ssh2_connect
	 */
	public function ssh2Connect()
	{
		return call_user_func_array($this->_underscore(__FUNCTION__), func_get_args());
	}

	/**
	 * Initialize SFTP subsystem
	 *
	 * @see ssh2_sftp
	 */
	public function ssh2Sftp()
	{
		return call_user_func_array($this->_underscore(__FUNCTION__), func_get_args());
	}

	/**
	 * Authenticate using a public key
	 *
	 * @see ssh2_auth_pubkey_file
	 */
	public function ssh2AuthPubkeyFile()
	{
		return call_user_func_array($this->_underscore(__FUNCTION__), func_get_args());
	}

	/**
	 * Authenticate over SSH using a plain password
	 *
	 * @see ssh2_auth_password
	 */
	public function ssh2AuthPassword()
	{
		return call_user_func_array($this->_underscore(__FUNCTION__), func_get_args());
	}
}
