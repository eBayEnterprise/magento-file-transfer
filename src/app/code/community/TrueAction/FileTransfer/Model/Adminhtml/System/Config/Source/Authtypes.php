<?php
class TrueAction_FileTransfer_Model_Adminhtml_System_Config_Source_Authtypes
{
	public function toOptionArray()
	{
		$helper = Mage::helper('filetransfer');
		return array(
			array('value' => 'password', 'label' => $helper->__('Password')),
			array('value' => 'pub_key',  'label' => $helper->__('Public Key'))
		);
	}

	public function toArray()
	{
		$helper = Mage::helper('filetransfer');
		return array(
			'password' => $helper->__('Password'),
			'pub_key'  => $helper->__('Public Key')
		);
	}
}