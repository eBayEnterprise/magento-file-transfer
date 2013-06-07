<?php
/**
 * these tests aren't unit tests (by definition) as they need external stuff
 * setup. as such these tests should not run by default and have to be run
 * manually.
 *
 * NOTE:
 * for these tests to work you must have a server listening on the localhost for
 * each protocol tested. a user must be setup as follows:
 *
 * username: test
 * password: welcome1
 *
 * NOTE:
 * the sftp test uses the keys included in fixtures/opensshkeys.
 */
class TrueAction_FileTransfer_Test_ConnectTests extends EcomDev_PHPUnit_Test_Case
{
	public function setUp()
	{
		@unlink('/tmp/foo.txt');
		@unlink('/tmp/3471_ftransfer_test.csv');
		@unlink('/tmp/3471_ftransfer_test2.csv');
	}

	/**
	 * @test
	 * @loadFixture sendfile
	 * @dataProvider dataProvider
	 */
	public function testConnectivity($protocol) {
		$model = Mage::helper('filetransfer')->getProtocolModel(
			'testsection/testgroup',
			$protocol
		);
		$result = $model->sendString(',,,,,', '3471_ftransfer_test.csv');
		$this->assertTrue($result);

		$result = $model->getString('3471_ftransfer_test.csv');
		$this->assertSame(',,,,,', $result);

		$result = $model->getFile(
			'/tmp/foo.txt',
			'3471_ftransfer_test.csv'
		);
		$this->assertTrue($result);

		$result = $model->sendFile(
			'/tmp/foo.txt',
			'3471_ftransfer_test2.csv'
		);
		$this->assertTrue($result);
	}

	/**
	 * @test
	 * @loadFixture testSftpKey
	 */
	public function testSftpKey() {
		$model = Mage::helper('filetransfer')->getProtocolModel(
			'testsection/testgroup',
			'sftp'
		);
		$result = $model->sendString(',,,,,', '3471_ftransfer_test.csv');
		$this->assertTrue($result);

		$result = $model->getString('3471_ftransfer_test.csv');
		$this->assertSame(',,,,,', $result);

		$result = $model->getFile(
			'/tmp/foo.txt',
			'3471_ftransfer_test.csv'
		);
		$this->assertTrue($result);

		$result = $model->sendFile(
			'/tmp/foo.txt',
			'3471_ftransfer_test2.csv'
		);
		$this->assertTrue($result);
	}

	/**
	 * this function only exists to encrypt values so that they can be copied
	 * into fixtures for encrypted config fields.
	 */
	public function encryptStuff()
	{
		$privateKey = '-----BEGIN RSA PRIVATE KEY-----
MIICWwIBAAKBgQCwCx216uAm5OF2304UpPIHQ0+yS3ZPnQ2Z44Oy4OD08kZRUZDP
nKG3vuuS/UCUqMlpD0gOs9DE+dtBD0e5OLuN5/AojrD3gk0U8haSXJD4b7QwsFmY
bzBdXAdtBITrTOKY2vwFJE6WQiWmMcbAPjkN2GXiJmuGABfb8VuddT6D7QIBJQKB
gDkYXKmz9au/2mu+C3xRK+auDAJ5Vs22ZUaqp0Du/NnSCPfHbTxqJpx1RXvHwesG
Tyj+CYg6UYv+AeuWQMZzQ77ITVVjauPQt+kp+iSW9F+Zf0XmbkS3CJUWU+sFDQ8N
h7Q9VAqZjF1T2ucKyCwp4ak9+OjUays+QqwL283DUVs9AkEA3gT6YZ6Td1RY0P12
0v0n3cImPdzFLza8VLy4+ksOtYg6RNisTTF23otqfw4gUqbrqpP1+anuWDiQ7hRq
Z04rJwJBAMr8vDqI0EdsV9+rKrgiVHipnZao1LZJ1VfH9Lqm7i2inYO1jX2sVPZh
81F6F7/FAwQSc53MfP08YzQMI0kK1MsCQQCcA3+C3iJvh2EBj4PuNVpIzZ5U/AAv
A95JYgVxnIbgbZC6wcUvUzDvaOMNLIVqgyIk1qzY8/OX8GXXu1GwYHFZAkEAmZyq
HnVfWKUEOo9emS68dvzl9XjYUpi9H9WWmxaKusA/2Use91H7G1ELKObF3Tsk4Hyq
hUDNSgQvZahSDcMB5QJAHeDbQJPWnhx/AeJ5bNYwuVL97V6pI+yWEbaqSnNChDy9
zv3PRJONM3nr0uMpTXRCgMT8ksmsw1/Z6obyfBTlog==
-----END RSA PRIVATE KEY-----
';
		$privateKey = Mage::helper('core')->encrypt($privateKey);
		print "\n\n'$privateKey'\n\n";

		$publicKey = 'ssh-rsa AAAAB3NzaC1yc2EAAAABJQAAAIEAsAsdtergJuThdt9OFKTyB0NPskt2T50NmeODsuDg9PJGUVGQz5yht77rkv1AlKjJaQ9IDrPQxPnbQQ9HuTi7jefwKI6w94JNFPIWklyQ+G+0MLBZmG8wXVwHbQSE60zimNr8BSROlkIlpjHGwD45Ddhl4iZrhgAX2/FbnXU+g+0= rsa-key-20130607';
		$publicKey = Mage::helper('core')->encrypt($publicKey);
		print "\n\n'$publicKey'\n\n";
	}
}