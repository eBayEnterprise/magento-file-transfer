<?php
/**
 * Adapter for Ftps (FTP over SSL)
 *
 * Basically just wraps php functions; In order to comply with coding standards, we camelCase
 * ftp_ssl_connect and then apply _underscore to them to make the correct call. This is how we 
 * can conform to sniff rules and make this as simple as possible.
 */
class TrueAction_FileTransfer_Model_Adapter_Ftps extends Varien_Object
{
	/**
	 * Opens an Secure SSL-FTP connection
	 *
	 * @see ftp_ssl_connect
	 */
	public function ftpSslConnect()
	{
		return call_user_func_array($this->_underscore(__FUNCTION__), func_get_args());
	}
}
