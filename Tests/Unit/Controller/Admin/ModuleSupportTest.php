<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Controller\Admin\ModuleSupport;
use Wirecard\Oxid\Core\Helper;

class ModuleSupportTest extends \Wirecard\Test\WdUnitTestCase
{
    /**
     * @var ModuleSupport
     */
    private $_moduleSupport;

    protected function setUp()
    {
        $this->_moduleSupport = oxNew(ModuleSupport::class);
        parent::setUp();
    }

    protected function failOnLoggedExceptions()
    {
        // don't fail on logged exception -> mailer is not instantiated
        $this->exceptionLogHelper->clearExceptionLogFile();
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

    /**
     * @dataProvider testSendSupportEmailActionWithParamsProvider
     */
    public function testSendSupportEmailActionWithParams($sText, $sFromEmail, $sReplyEmail, $sErrorText)
    {
        $this->_moduleSupport->setEditObjectId(Helper::MODULE_ID);
        $_POST['module_support_text'] = $sText;
        $_POST['module_support_email_from'] = $sFromEmail;
        $_POST['module_support_email_reply'] = $sReplyEmail;

        $this->_moduleSupport->sendSupportEmailAction();

        $this->assertEquals($sErrorText, $this->_moduleSupport->getViewData()['alertMessage']);
    }

    public function testSendSupportEmailActionWithParamsProvider()
    {
        return [
            'correct params' => ['support text', 'from@email.com', 'reply@email.com', Helper::translate('wd_support_send_error')],
            'correct params without reply email' => ['support text', 'from@email.com', null, Helper::translate('wd_support_send_error')],
            'incorrect "from" email' => ['support text', 'from', 'reply@email.com', Helper::translate('wd_enter_valid_email_error')],
            'incorrect "reply to" email' => ['support text', 'from@email.com', 'reply', Helper::translate('wd_enter_valid_email_error')],
            'no body failure' => [null, 'from@email.com', 'reply@email.com', Helper::translate('wd_message_empty_error')],
        ];
    }
}
