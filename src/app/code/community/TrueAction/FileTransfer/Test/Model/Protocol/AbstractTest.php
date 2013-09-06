<?php
/**
 * TrueAction_FileTransfer_Test_Model_Config_Ftp
 * tests for the ftp config generator.
 */

class TrueAction_FileTransfer_Test_Model_Protocol_AbstractTest extends EcomDev_PHPUnit_Test_Case
{
	const STRING_DATA = 'The Munsters is an American television sitcom depicting the home life of a family of benign monsters.';
	const FAKE_HOST   = 'host';
	const FAKE_PORT   = '13';
	const FAKE_USER   = 'herman';
	const FAKE_PASS   = 'munster';

	public function setUp()
	{
		$this->cls = new ReflectionClass(
			'TrueAction_FileTransfer_Model_Protocol_Abstract'
		);
		$this->getCodes = $this->cls->getMethod(
			'getCodes'
		);
	}

	/**
	 * Test concrete methods
	 *
	 * @test
	 */
	public function testConcreteMethods()
	{
		$model = $this->getMockForAbstractClass('TrueAction_FileTransfer_Model_Protocol_Abstract');
		$this->assertInstanceOf('TrueAction_FileTransfer_Model_Protocol_Abstract', $model);

		// Some setting, make sure it chains
		$chain = $model
			->setHost(self::FAKE_HOST)
			->setPort(self::FAKE_PORT)
			->setUsername(self::FAKE_USER)
			->setPassword(self::FAKE_PASS);
		$this->assertInstanceOf(get_class($model), $chain);

		// Assert sets resulted in proper gets
		$this->assertSame($model->getConfig()->getHost(), self::FAKE_HOST);
		$this->assertSame($model->getConfig()->getPort(), self::FAKE_PORT);
		$this->assertSame($model->getConfig()->getUsername(), self::FAKE_USER);
	}

	/**
	 * Test protected method
	 *
	 * @todo: Perhaps a better way to do this.
	 * @test
	 */
	public function testProtectedMethod()
	{
		$dummy = new DummyClass();
		$dataString = $dummy->testGetDataUriFromString(self::STRING_DATA);
		$this->assertStringStartsWith('data:text/plain,', $dataString);
	}

	/**
	 * @test
	 * */
	public function testListProtocols()
	{
		$ls = $this->getCodes->invoke(null);
		$this->assertContains('sftp', $ls);
		$this->assertNotContains('.', $ls);
		$this->assertNotContains('..', $ls);
		$lsTwo = $this->getCodes->invoke(null);
		$this->assertSame($ls, $lsTwo);
	}
}

/**
 * getMockForAbstractClass specifically lets me test concrete methods of an abstract class,
 * but I can't get to protected members.
 * @todo: Is '_getDataUriFromString' being 'protected' the right thing to do?
 *
 */
class DummyClass extends TrueAction_FileTransfer_Model_Protocol_Abstract
{
	/**
	 * To cover the protected member '_getDataUriFromString'
	 * @todo: Probabaly a better way to do this
	 */
	public function testGetDataUriFromString($string)
	{
		return $this->_getDataUriFromString($string);
	}

	/**
	 * Required to fulfill contract
	 */
	public function sendFile($remoteFile, $localFile)
	{
		return;
	}

	/**
	 * Required to fulfill contract
	 */
	public function getFile($remoteFile, $localFile)
	{
		return;
	}
}
