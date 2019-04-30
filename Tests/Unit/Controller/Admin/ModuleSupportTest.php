<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

class ModuleSupportTest extends \Wirecard\Test\WdUnitTestCase
{
    /**
     * @var \Wirecard\Oxid\Controller\Admin\ModuleSupport
     */
    private $_moduleSupport;

    protected function setUp()
    {
        $this->_moduleSupport = oxNew(\Wirecard\Oxid\Controller\Admin\ModuleSupport::class);
        parent::setUp();
    }

    public function testRender()
    {
        $this->_moduleSupport->render();
        $this->assertArrayHasKey('oxid', $this->_moduleSupport->getViewData());
        $this->assertArrayHasKey('contactEmail', $this->_moduleSupport->getViewData());
        $this->assertArrayHasKey('defaultEmail', $this->_moduleSupport->getViewData());
        $this->assertArrayHasKey('isOurModule', $this->_moduleSupport->getViewData());
    }

    public function testSendSupportEmailActionWithoutParams()
    {
        $this->_moduleSupport->sendSupportEmailAction();
        $this->assertArrayHasKey('alertMessage', $this->_moduleSupport->getViewData());
        $this->assertArrayHasKey('alertType', $this->_moduleSupport->getViewData());
    }

    public function testSendSupportEmailActionWithCorrectParams()
    {
        $this->markTestIncomplete(
            '$this->getConfig()->getRequestParameter(\'module_support_text\') not working correctly'
        );
        $_POST['module_support_text'] = 'support text';
        $_POST['module_suppot_email_from'] = 'from@email.com';
        $_POST['module_support_email_reply'] = 'reply@emil.com';

        $this->_moduleSupport->sendSupportEmailAction();
    }
}
