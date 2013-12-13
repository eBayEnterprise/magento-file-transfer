<?php
class TrueAction_FileTransfer_Test_Block_System_Config_Form_Field_FileTest extends EcomDev_PHPUnit_Test_Case
{
    /**
     * verify an empty string is always returned
     */
    protected function testGetDeleteCheckbox()
    {
        $testModel = $this->getBlockMockBuilder('filetransfer/system_config_form_field_file')
            ->disableOriginalConstructor()
            ->setMethods(array('none'))
            ->getMock();
        $testModel->setValue('foo');
        $testMethod = new ReflectionMethod($testModel, '_getDeleteCheckbox');
        $testMethod->setAccessible(true);
        $this->assertSame(
            '<div>foo</div>',
            $testModel->invoke($testModel)
        );
    }
}
