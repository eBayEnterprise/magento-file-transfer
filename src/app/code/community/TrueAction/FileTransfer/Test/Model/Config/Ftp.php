<?php
/*
TrueAction_ActiveConfig_Model_Generator

Interface class for all configuration generators
 */
class TrueAction_FileTransfer_Test_Model_Config_Ftp
	extends EcomDev_PHPUnit_Test_Case
{
	public function setUp()
	{
		$this->class = new ReflectionClass(
			'TrueAction_FileTransfer_Model_Config_Ftp'
		);
	}

	/**
	 * @test
	 * */
	public function testGetConfig() {
		$model = new TrueAction_FileTransfer_Model_Config_Ftp();
		$importOptions = null;
		$config = $model->getConfig($importOptions);
		$this->assertInstanceOf('Varien_Simplexml_Config', $config);
		$this->assertTrue($config->hasChildren());

		$fieldsXml = '
			<fields>
			<ftp_username translate="label">
				<label>Username</label>
				<frontend_type>text</frontend_type>
			</ftp_username>
			<ftp_password translate="label">
				<label>Password</label>
				<frontend_type>obscure</frontend_type>
				<backend_model>adminhtml/system_config_backend_encrypted</backend_model>
			</ftp_password>
			<ftp_host translate="label">
				<label>Remote Host</label>
				<frontend_type>text</frontend_type>
			</ftp_host>
			<ftp_port translate="label">
				<label>Remote Port</label>
				<frontend_type>text</frontend_type>
			</ftp_port>
			</fields>
		';
	}
}
