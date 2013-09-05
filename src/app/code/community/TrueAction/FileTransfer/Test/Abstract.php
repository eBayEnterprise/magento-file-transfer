<?php
abstract class TrueAction_FileTransfer_Test_Abstract extends EcomDev_PHPUnit_Test_Case
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
				->will($this->returnValue($returnSet));
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
