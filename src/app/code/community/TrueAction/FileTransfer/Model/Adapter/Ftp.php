<?php
/**
 * Adapter for Ftp
 *
 * Wraps php functions; in order to comply with coding standards, we camelCase
 * stream_get_contents and the ftp_xx functions, and then apply _underscore to them to make the 
 * correct call. This is how we can conform to sniff rules and make this as simple as possible.
 */
class TrueAction_FileTransfer_Model_Adapter_Ftp extends Varien_Object
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
	 * Open a stream
	 *
	 * @see fopen
	 */
	public function fopen()
	{
		return call_user_func_array(__FUNCTION__, func_get_args());
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
	 * Close ftp 
	 *
	 * @see ftp_close
	 */
	public function ftpClose()
	{
		return call_user_func_array($this->_underscore(__FUNCTION__), func_get_args());
	}

	/**
	 * Opens an FTP connection 
	 *
	 * @see ftp_connect
	 */
	public function ftpConnect()
	{
		return call_user_func_array($this->_underscore(__FUNCTION__), func_get_args());
	}

	/**
	 * Downloads a file from the FTP server and saves to an open file
	 *
	 * @see ftp_fget
	 */
	public function ftpFget()
	{
		return call_user_func_array($this->_underscore(__FUNCTION__), func_get_args());
	}

	/**
	 * Uploads from an open file to the FTP server
	 *
	 * @see ftp_fput
	 */
	public function ftpFput()
	{
		return call_user_func_array($this->_underscore(__FUNCTION__), func_get_args());
	}

	/**
	 * Logs in to an FTP connection
	 *
	 * @see ftp_login
	 */
	public function ftpLogin()
	{
		return call_user_func_array($this->_underscore(__FUNCTION__), func_get_args());
	}

	/**
	 * Turns passive mode on or off
	 *
	 * @see ftp_pasv
	 */
	public function ftpPasv()
	{
		return call_user_func_array($this->_underscore(__FUNCTION__), func_get_args());
	}
}
