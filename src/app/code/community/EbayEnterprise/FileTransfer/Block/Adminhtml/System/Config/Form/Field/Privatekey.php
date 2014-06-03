<?php
/**
 * Copyright (c) 2013-2014 eBay Enterprise, Inc.
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @copyright   Copyright (c) 2013-2014 eBay Enterprise, Inc. (http://www.ebayenterprise.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

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
