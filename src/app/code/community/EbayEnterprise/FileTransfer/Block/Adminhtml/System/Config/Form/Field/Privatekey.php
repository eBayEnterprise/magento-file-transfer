<?php
/**
 * This class is block (but is specified as a 'frontend_model' it the config.xml)
 * which effectively overrides lib/Varien/Data/Form/Element/Textarea.php so that we
 * can special case what we wish to display.
 *
 * Consult Varien_Data_Form_Element_Abstract for other methods available in the '$element'
 */
class EbayEnterprise_FileTransfer_Block_Adminhtml_System_Config_Form_Field_Privatekey
	extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		$element->addClass('textarea');
		$html = '<textarea id="' . $element->getHtmlId()
					. '" name="'.$element->getName()
					.'" ' . ' placeholder="' . $element->getEscapedValue() . '" >'
					. "</textarea>";
		return $html;
	}
}
