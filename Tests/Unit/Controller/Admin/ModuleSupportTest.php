<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Core\Exception\StandardException;
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
    public function testSendSupportEmailActionWithParams($sText, $sFromEmail, $sReplyEmail, $sErrorText, $bHandleException)
    {
        $this->_moduleSupport->setEditObjectId(Helper::MODULE_ID);
        $_POST['module_support_text'] = $sText;
        $_POST['module_support_email_from'] = $sFromEmail;
        $_POST['module_support_email_reply'] = $sReplyEmail;

        $this->_moduleSupport->sendSupportEmailAction();

        if ($bHandleException) {
            $this->assertLoggedException(StandardException::class, "Could not instantiate mail function.");
        }

        $this->assertEquals($sErrorText, $this->_moduleSupport->getViewData()['alertMessage']);
    }

    public function testSendSupportEmailActionWithParamsProvider()
    {
        return [
            'correct params' => ['support text', 'from@email.com', 'reply@email.com', Helper::translate('support_send_error'), true],
            'correct params without reply email' => ['support text', 'from@email.com', null, Helper::translate('support_send_error'), true],
            'incorrect "from" email' => ['support text', 'from', 'reply@email.com', Helper::translate('enter_valid_email_error'), false],
            'incorrect "reply to" email' => ['support text', 'from@email.com', 'reply', Helper::translate('enter_valid_email_error'), false],
            'no body failure' => [null, 'from@email.com', 'reply@email.com', Helper::translate('message_empty_error'), false],
        ];
    }
}
