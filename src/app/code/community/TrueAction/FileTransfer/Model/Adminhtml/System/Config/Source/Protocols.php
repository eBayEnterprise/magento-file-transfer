<?php
class TrueAction_FileTransfer_Model_Adminhtml_System_Config_Source_Protocols
{
	public function toOptionArray()
	{
		$helper = Mage::helper('filetransfer');
		$list = array();
		$protocolCodes = TrueAction_FileTransfer_Model_Protocol_Abstract::getCodes();
		foreach ($protocolCodes as $code) {
			$model = Mage::getModel('filetransfer/protocol_types_' . $code);
			$list[] = array(
				'value' => $code,
				'label' => $helper->__($model->getName())
			);
		}
		return $list;
	}

	public function toArray()
	{
		$helper = Mage::helper('filetransfer');
		$list = array();
		$protocolCodes = TrueAction_FileTransfer_Model_Protocol_Abstract::getCodes();
		foreach ($protocolCodes as $code) {
			$model = Mage::getModel('filetransfer/protocol_types_' . $code);
			$list[] = array($code => $helper->__($model->getName()));
		}
		return $list;
	}
}
