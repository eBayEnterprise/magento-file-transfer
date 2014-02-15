<?php
class TrueAction_FileTransfer_Test_Model_Protocol_ConfigTest extends EcomDev_PHPUnit_Test_Case
{
	private static $_config = array(
		'protocol_code' => 'ftp',
		'config_path' => 'testsection/testgroup'
	);

	private static $_fieldMap = array(
		'filetransfer_ftp_username'    => 'username',
		'filetransfer_ftp_password'    => 'password',
		'filetransfer_ftp_host'        => 'host',
		'filetransfer_ftp_port'        => 'port',
		'filetransfer_ftp_remote_path' => 'remote_path',
	);

	public function setUp()
	{
		$this->class = new ReflectionClass(
			'TrueAction_FileTransfer_Model_Protocol_Config'
		);
		$this->importOptions = new Varien_Simplexml_Config(
			'<filetransfer>
				<sort_order>190</sort_order>
				<show_in_default>1</show_in_default>
				<show_in_website>1</show_in_website>
				<show_in_store>1</show_in_store>
				<ftp></ftp>
				<sftp></sftp>
			</filetransfer>'
		);
	}

	/**
	 * verify the configuration is generated.
	 * @test
	 */
	public function testGenerateConfig()
	{
		$model = $this->getModelMockBuilder('filetransfer/protocol_config')
			->disableOriginalConstructor()
			->setMethods(array('none'))
			->getMock();
		$config = $model->setConfigPath('testsection/testgroup')
			->setProtocolCode('ftp')
			->generateFields($this->importOptions);
		$this->assertInstanceOf('Varien_Simplexml_Config', $config);
		$node = $config->getNode();
		$this->assertInstanceOf('Varien_Simplexml_Element', $node);
		$this->assertTrue($node->hasChildren());
		$names = array();
		foreach ($node as $name => $node) {
			$this->assertInstanceOf('Varien_Simplexml_Element', $node);
			$names[] = $name;
		}
		$this->assertContains('filetransfer_ftp_username', $names);
		$this->assertContains('filetransfer_ftp_password', $names);
		$this->assertContains('filetransfer_ftp_host', $names);
		$this->assertContains('filetransfer_ftp_port', $names);
		$this->assertContains('filetransfer_ftp_remote_path', $names);
	}

	/**
	 * verify the configuration is read correctly
	 * @test
	 * @loadFixture ftpConfig
	 */
	public function testConfigValues()
	{
		$helper = $this->getHelperMock('core/data', array('decrypt'));
		$helper->expects($this->any())
			->method('decrypt')
			->with($this->identicalTo('0:2:3fbb7c95e7c84df0:ZA2CwPF1DexZjAOEXMLcxA=='))
			->will($this->returnValue('welcome1'));
		$this->replaceByMock('helper', 'core', $helper);
		$cfg = $this->getModelMockBuilder('filetransfer/protocol_config')
			->disableOriginalConstructor()
			->setMethods(array('none'))
			->getMock();
		$cfg->setData(self::$_config);
		$this->assertSame('testsection/testgroup', $cfg->getConfigPath());
		$this->assertSame('ftp', $cfg->getProtocolCode());
		$cfg->loadMappedFields(self::$_fieldMap);
		$this->assertSame('somename', $cfg->getUsername());
		$this->assertSame('welcome1', $cfg->getPassword());
		$this->assertSame('some.host', $cfg->getHost());
		$this->assertSame('21', $cfg->getPort());
		$this->assertSame('/', $cfg->getRemotePath());
	}

	/**
	 * verify getUrl generates a url using the data stored in the model.
	 * @test
	 * @dataProvider dataProvider
	 */
	public function testGetUrl($data, $includePath, $url)
	{

		$config = $this->getModelMockBuilder('filetransfer/protocol_config')
			->disableOriginalConstructor()
			->setMethods(array('none'))
			->getMock();
		$config->setData($data);
		$this->assertSame($url, $config->getUrl($includePath));
	}

	/**
	 * verify TrueAction_FileTransfer_Exception_Configuration is thrown when the protocol code doesn't match a known code.
	 * @test
	 */
	public function testValidateProtocolCodeException()
	{
		$helper = $this->getHelperMockBuilder('filetransfer/data')
			->disableOriginalConstructor()
			->setMethods(array('getProtocolCodes'))
			->getMock();
		$helper->expects($this->atLeastOnce())
			->method('getProtocolCodes')
			->will($this->returnValue(array()));
		$this->replaceByMock('helper', 'filetransfer', $helper);

		$testModel = $this->getModelMockBuilder('filetransfer/protocol_config')
			->disableOriginalConstructor()
			->setMethods(array('getProtocolCode'))
			->getMock();
		$testModel->expects($this->atLeastOnce())
			->method('getProtocolCode')
			->will($this->returnValue(null));
		$this->setExpectedException(
			'TrueAction_FileTransfer_Exception_Configuration',
			'FileTransfer Config Error: Invalid Protocol Code'
		);
		$method = new ReflectionMethod($testModel, '_validateProtocolCode');
		$method->setAccessible(true);
		$method->invoke($testModel);
	}
	/**
	 * @test
	 */
	public function testGetBaseFields()
	{
		$config = $this->getModelMockBuilder('filetransfer/protocol_config')
			->disableOriginalConstructor()
			->setMethods(array('getProtocolCode'))
			->getMock();
		$config->expects($this->once())
			->method('getProtocolCode')
			->will($this->returnValue('SFTP'));

		$fields = $config->getBaseFields();

		$this->assertInstanceOf('Varien_Simplexml_Config', $fields);

		$this->assertSame(
			preg_replace('/[ ]{2,}|[\t]/', '', str_replace(array("\r\n", "\r", "\n"), '',
			'<fields>
				<filetransfer_protocol translate="label">
				<label>Protocol</label>
				<frontend_type>select</frontend_type>
				<source_model>filetransfer/adminhtml_system_config_source_protocols</source_model></filetransfer_protocol>
				<filetransfer_SFTP_username translate="label">
					<label>Username</label>
					<frontend_type>text</frontend_type>
					<depends>
						<filetransfer_protocol>SFTP</filetransfer_protocol>
					</depends>
				</filetransfer_SFTP_username>
				<filetransfer_SFTP_password translate="label">
					<label>Password</label>
					<frontend_type>obscure</frontend_type>
					<backend_model>adminhtml/system_config_backend_encrypted</backend_model>
					<depends>
						<filetransfer_protocol>SFTP</filetransfer_protocol>
					</depends>
				</filetransfer_SFTP_password>
				<filetransfer_SFTP_host translate="label">
					<label>Remote Host</label>
					<frontend_type>text</frontend_type>
					<depends>
						<filetransfer_protocol>SFTP</filetransfer_protocol>
					</depends>
				</filetransfer_SFTP_host>
				<filetransfer_SFTP_port translate="label">
					<label>Remote Port</label>
					<frontend_type>text</frontend_type>
					<depends>
						<filetransfer_protocol>SFTP</filetransfer_protocol>
					</depends>
				</filetransfer_SFTP_port>
				<filetransfer_SFTP_remote_path translate="label">
					<label>Remote Path</label>
					<frontend_type>text</frontend_type>
					<depends>
						<filetransfer_protocol>SFTP</filetransfer_protocol>
					</depends>
				</filetransfer_SFTP_remote_path>
			</fields>'
			)),
			$fields->getXmlString()
		);
	}

	/**
	 * verify the protocol code check wont fail when the desired code is at index 0 in the array.
	 * @test
	 */
	public function testValidateProtocolCodeIndexZero()
	{
		$helper = $this->getHelperMock('filetransfer/data', array('getProtocolCodes'));
		$helper->expects($this->once())
			->method('getProtocolCodes')
			->will($this->returnValue(array( 0 => 'the_code')));
		$this->replaceByMock('helper', 'filetransfer', $helper);
		$config = $this->getModelMockBuilder('filetransfer/protocol_config')
			->disableOriginalConstructor()
			->setMethods(array('getProtocolCode'))
			->getMock();
		$config->expects($this->atLeastOnce())
			->method('getProtocolCode')
			->will($this->returnValue('the_code'));
		$ctor = new ReflectionMethod($config, '_validateProtocolCode');
		$ctor->setAccessible(true);
		$ctor->invoke($config);
	}

	/**
	 * verify config model attempts to load config data into fields and validate the protocol code.
	 * @test
	 */
	public function testConstructor()
	{
		$config = $this->getModelMockBuilder('filetransfer/protocol_config')
			->disableOriginalConstructor()
			->setMethods(array('loadMappedFields', '_validateProtocolCode'))
			->getMock();
		$config->expects($this->once())
			->method('loadMappedFields')
			->will($this->returnSelf());
		$config->expects($this->once())
			->method('_validateProtocolCode')
			->will($this->returnSelf());
		EcomDev_Utils_Reflection::invokeRestrictedMethod($config, '_construct');
	}
	/**
	 * Verify the configuration falls back to the most specific setting
	 * for show_in_default, show_in_website and show_in_store.
	 *
	 * @test
	 * @dataProvider dataProvider
	 * @param array $global least specific show_in_* triple
	 * @param array $spec show_in_* triple more specific than $global
	 * @param array $field most specific show_in_* triple
	 * @param array $expected result
	 */
	public function testGenerateFieldsShowFlags(array $global, array $spec, array $field, array $expected) {
		list($glbDef, $glbWeb, $glbStr) = $global;
		list($spcDef, $spcWeb, $spcStr) = $spec;
		list($fldDef, $fldWeb, $fldStr) = $field;
		list($expDef, $expWeb, $expStr) = $expected;
		$hlpr = $this->getHelperMock('filetransfer/data', array(
			'getGlobalSortOrder',
			'getGlobalShowInDefault',
			'getGlobalShowInWebsite',
			'getGlobalShowInStore',
		));
		$hlpr->expects($this->any())
			->method('getGlobalSortOrder')
			->will($this->returnValue(10));
		$hlpr->expects($this->any())
			->method('getGlobalShowInDefault')
			->will($this->returnValue($glbDef));
		$hlpr->expects($this->any())
			->method('getGlobalShowInWebsite')
			->will($this->returnValue($glbWeb));
		$hlpr->expects($this->any())
			->method('getGlobalShowInStore')
			->will($this->returnValue($glbStr));
		$this->replaceByMock('helper', 'filetransfer', $hlpr);
		$spc = Mage::getModel('core/config');
		$spc->loadString('<filetransfer/>');
		$spc->setNode('show_in_default', $spcDef)
			->setNode('show_in_website', $spcWeb)
			->setNode('show_in_store', $spcStr);
		$flds = Mage::getModel('core/config');
		$flds->loadString('<fields><field/></fields>');
		$flds->setNode('field/show_in_default', $fldDef)
			->setNode('field/show_in_website', $fldWeb)
			->setNode('field/show_in_store', $fldStr);
		$model = $this->getModelMockBuilder('filetransfer/protocol_config')
			->disableOriginalConstructor()
			->setMethods(array('getBaseFields'))
			->getMock();
		$model->expects($this->once())
			->method('getBaseFields')
			->will($this->returnValue($flds));
		$resultNode = $model->generateFields($spc->getNode())->getNode('field');
		$failPat = 'Expecting show_in_%s of %d, but got %d instead.';
		$this->assertTrue($resultNode->is('show_in_default', $expDef), sprintf($failPat, 'default', $expDef, $resultNode->show_in_default));
		$this->assertTrue($resultNode->is('show_in_website', $expWeb), sprintf($failPat, 'website', $expWeb, $resultNode->show_in_website));
		$this->assertTrue($resultNode->is('show_in_store', $expStr), sprintf($failPat, 'store', $expStr, $resultNode->show_in_store));
	}
}
