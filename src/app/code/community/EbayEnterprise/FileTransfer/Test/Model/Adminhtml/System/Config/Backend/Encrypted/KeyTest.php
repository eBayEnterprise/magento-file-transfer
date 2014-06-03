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

class EbayEnterprise_FileTransfer_Test_Model_Adminhtml_System_Config_Backend_Encrypted_KeyTest
	extends EcomDev_PHPUnit_Test_Case
{
	const MODEL_UNDER_TEST = 'filetransfer/adminhtml_system_config_backend_encrypted_key';

	const VALID_TEST_PUBLIC_KEY =
"-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArcpMSN1Sg5QmpnL4PYdY
snmQZMspQZyxVTi/j7z5OuJaVoCRb2YXBJzihkjHvmhrWfc951vmjUu9Uz4LrVW2
3j+YP7eDQya9VmaHz7uxk4VHK2AFFABFjN0YZTN9jkCz7CHiSn1paMj9Ib413P8H
12wimrDyNxg9GhExxsCLi7nZnTrd2y31SACGDNd/VvF6aWBgpVMn7CWqKyfPczqe
Uc2c834tB9WcwL7mcADdQh59DP9Z0KpEKKr2EFNJ9GX5wI2Cuy2PvbtbXZOuXkfj
rwcv5eqPIimxwKRiWZDGF7sGbx6ZiKNp/18f6E3kdn7THEbzfmxf1yq/9oN2UDwo
oQIDAQAB
-----END PUBLIC KEY-----
";

	const VALID_TEST_PRIVATE_KEY   =
"-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEArcpMSN1Sg5QmpnL4PYdYsnmQZMspQZyxVTi/j7z5OuJaVoCR
b2YXBJzihkjHvmhrWfc951vmjUu9Uz4LrVW23j+YP7eDQya9VmaHz7uxk4VHK2AF
FABFjN0YZTN9jkCz7CHiSn1paMj9Ib413P8H12wimrDyNxg9GhExxsCLi7nZnTrd
2y31SACGDNd/VvF6aWBgpVMn7CWqKyfPczqeUc2c834tB9WcwL7mcADdQh59DP9Z
0KpEKKr2EFNJ9GX5wI2Cuy2PvbtbXZOuXkfjrwcv5eqPIimxwKRiWZDGF7sGbx6Z
iKNp/18f6E3kdn7THEbzfmxf1yq/9oN2UDwooQIDAQABAoIBACcTpbN8kGElnssu
bsLm+/qleuIvDEfEg9s1t10KkL+8xbNNlWYG/oX7ALRRCRi3Qewou7KZ52096oQd
H2MKMuQmSIWLLeibfVdAFqmO+o7BGQ+Xt4yXwwu5axLWURT7V3lw5QD60gjNqJ09
t77JWWoG1oER6GSa/qIt25NlF/uCnzyX8M7xbTjki/l5LFJi8ixVbtXkOjJYLeXa
wSuRxVN6HTJMtPS+1OSdrsNyZOhxX6wRVI6zf8TlmBsA6FC9DblwD25fM0oqrSSt
SFWCdPBVtDoPVtRcWIiE0jV3SMCrh11mcxaABiwZX5qiFVpOYZ4yXWLaifY/ucnI
65hoN0ECgYEA5vqsAY1gX08lyK8RefWUmkxXaZMbMqzObSG1vyAK9LV6bS435RD2
bvRHcVgw4j2Y4gPeeDT1hmI0dq861puhZQHUBttYKivGcBXVm9g6YfqDrTFGth3c
gShoEcxHUHuVpt9VNfxl03iaAO5POZK+5cp0iW2VWZP5Zr0EEOyKizsCgYEAwJ21
Q5XBbBlYSuG26RsNTM7L5etAS5QTsLOvF7hP4+QMS6D3xUxq0x/PO9BbXuUA4NkG
L7bOVxAHXbxjKe/iWW1SNQWo5P2P5J7XI/yNvflDOQz4DcPCitjEmyTjM5h1SSt7
RiMePIkAZp7LhfveBHMbmB3ELbgLi6XawEJFxdMCgYEAyULpN90JeWLMyIYLU1qy
ZpRYomyFCW3b3Om/pM713O54w8O+/oD+SgXebpvq1GfZ3C6E3fc/bR4LGtNrEG7B
ffLO3j6oHu7P1QChhU8u6ArSS8ohFDUG2x/rNn7qMO3Oo338kLLhwxdWEbOVItSE
NFRpoQn0Vf6DFYtjjJ+fxNsCgYByZ7nYUMS3/j3RDEvmHOlDa7jz8U0ZFvSzCabA
AfuBslwTN6KzD3aLu+MM9e6vaHmjE4R3Jq9cSur1JAYKTK82ypX/ZEMy7+BdvHKw
rztJURo6cpeLJXERozrzo29HoBBZy3fG6uj0r7MLQNpF1JnELtJ/AX8aYKyK35IU
i8iBfQKBgDOMs8LuwmRJ3qcHzLLvRgNwo2AWceDrTcRJgN1U9KDNE16w/SZD6f1A
Zthuy8iYl2KqKEdn8kad1dbkZ+pTzGOhyOFlGmpPHtV2TioObtQL85W+Dmch9u4Y
q1WA3qssq5WCldpmQ8pMfLE/mRYvtbGP7rArdENEHlWJ1bsiXHGN
-----END RSA PRIVATE KEY-----";

	/**
	 * Given a valid key, we should call addNotice and return the security mask from getValue
	 * @test
	 */
	public function testValidKey()
	{
		$testModel = Mage::getModel($this::MODEL_UNDER_TEST);

		$session = $this->getModelMockBuilder($testModel::SESSION_KEY)
			->disableOriginalConstructor()
			->setMethods(array( 'addNotice',))
			->getMock();

		// addNotice will be called once, when the VALID_TEST_PRIVATE_KEY is passed
		$session->expects($this->exactly(1))
			->method('addNotice')
			->will($this->returnValue(true));
		$this->replaceByMock('singleton', $testModel::SESSION_KEY, $session);

		$testModel->setValue($this::VALID_TEST_PRIVATE_KEY)->_beforeSave(); // Calls addNotice
		// With a valid key, _afterLoad will set the display value to the public key
		$this->assertSame(
			self::VALID_TEST_PUBLIC_KEY,
			$testModel->_afterLoad()->getValue()
		);
	}
	/**
	 * When we are given an invalid value and we do not have an existing value, we should instigate an addError - this means
	 * we have no values at all.
	 * @test
	 */
	public function testNewKeyInvalidSoWeIssueError()
	{
		$testModel = $this->getModelMockBuilder($this::MODEL_UNDER_TEST)
			->setMethods(array('getOldValue',))
            ->getMock();
		$testModel->expects($this->exactly(1))
			->method('getOldValue')
			->will($this->returnValue(''));

		$session = $this->getModelMockBuilder($testModel::SESSION_KEY)
			->disableOriginalConstructor()
			->setMethods(array('addError',))
			->getMock();

		// addError will be called once, when _beforeSave realizes it doesn't have a pre-existing key,
		// and that the 1 passed in is invalid.
		$session->expects($this->exactly(1))
			->method('addError')
			->will($this->returnValue(true));
		$this->replaceByMock('singleton', $testModel::SESSION_KEY, $session);

		$testModel->setValue('not a key')->_beforeSave();

		// Assert that when we have no key, we get empty back
		$this->assertSame(
			'',
			$testModel->_afterLoad()->getValue()
		);
	}
	/**
	 * When we are given an invalid value and we have an existing value, we should instigate an addWarning - this means
	 * we are reverting back to an old value.
	 * @test
	 */
	public function testNewKeyInvalidSoWeKeepOriginal()
	{
		$testModel = $this->getModelMockBuilder($this::MODEL_UNDER_TEST)
			->setMethods(array('getOldValue',))
            ->getMock();
		$testModel->expects($this->exactly(1))
			->method('getOldValue')
			->will($this->returnValue($this::VALID_TEST_PRIVATE_KEY));

		$session = $this->getModelMockBuilder($testModel::SESSION_KEY)
			->disableOriginalConstructor()
			->setMethods(array( 'addWarning',))
			->getMock();

		// addWarning will be called once, when _beforeSave realizes it has a pre-existing valid key
		// that we tried to replace with an invalid key.
		$session->expects($this->exactly(1))
			->method('addWarning')
			->will($this->returnValue(true));
		$this->replaceByMock('singleton', $testModel::SESSION_KEY, $session);

		$testModel->setValue('not a key')->_beforeSave();
	}
}
