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

abstract class EbayEnterprise_FileTransfer_Test_Abstract extends EcomDev_PHPUnit_Test_Case
{
	/**
	 * Returns a mocked object
	 *
	 * @param a Magento Class Alias
	 * @param array of key / value pairs; key is the method name, value is value returned by that method
	 * @return mocked-object
	 */
	protected function _getFullMocker($classAlias, $mockedMethodSet, $disableConstructor=true)
	{
		$mockMethodNames = array_keys($mockedMethodSet);
		if( $disableConstructor ) {
			$mock = $this->getModelMockBuilder($classAlias)
				->disableOriginalConstructor()
				->setMethods($mockMethodNames)
				->getMock();
		} else {
			$mock = $this->getModelMockBuilder($classAlias)
				->setMethods($mockMethodNames)
				->getMock();
		}
		foreach($mockedMethodSet as $method => $returnSet ) {
			$mock->expects($this->any())
				->method($method)
				->will($returnSet);
		}
		return $mock;
	}

	/**
	 * Returns a mocked object, original model constructor disabled - you get only the methods you mocked.
	 *
	 * @param a Magento Class Alias
	 * @param array of key / value pairs; key is the method name, value is value returned by that method
	 * @param disableOriginalConstructor	true or false, defaults to true
	 *
	 * @return mocked-object
	 */
	public function replaceModel($classAlias, $mockedMethodSet, $disableOriginalConstructor=true)
	{
		$mock = $this->_getFullMocker($classAlias, $mockedMethodSet, $disableOriginalConstructor);
		$this->replaceByMock('model', $classAlias, $mock);
		return $mock;
	}
}
