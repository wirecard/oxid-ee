<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Module\Module;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Extend\Core\Email;

use Exception;

/**
 * Controls the view for the Module support tab in Module detail.
 *
 * @since 1.0.0
 */
class ModuleSupport extends AdminController
{
    /**
     * @inheritdoc
     *
     * @since 1.0.0
     */
    protected $_sThisTemplate = 'module_support.tpl';

    /**
     * current module
     *
     * @var OxidEsales\Eshop\Core\Module\Module
     *
     * @since 1.0.0
     */
    protected $_oModule;

    /**
     * @inheritdoc
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function render()
    {
        $sModuleId = $this->getEditObjectId();
        $sDefaultEmail = $this->getConfig()->getActiveShop()->oxshops__oxinfoemail->value;

        $this->_aViewData += [
            'oxid' => $sModuleId,
            'contactEmail' => $this->_getModule()->getInfo('email'),
            'defaultEmail' => $sDefaultEmail,
            'isOurModule' => Helper::isThisModule($sModuleId),
        ];

        if (empty($this->_aViewData['fromEmail'])) {
            $this->_aViewData['fromEmail'] = $sDefaultEmail;
        }

        return $this->_sThisTemplate;
    }

    /**
     * Action triggered from form to send support email
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function sendSupportEmailAction()
    {
        try {
            $this->_validateRequest();
        } catch (Exception $e) {
            $this->_aViewData += [
                'alertMessage' => $e->getMessage(),
                'alertType' => 'error',
            ];
            return;
        }

        $aEmailData = [];

        $this->_addDataFromForm($aEmailData);
        $this->_addShopData($aEmailData);

        $this->_sendEmail($aEmailData);
    }

    /**
     * Creates the email object and tries to send it
     *
     * @param array $aEmailData
     *
     * @since 1.0.0
     */
    protected function _sendEmail($aEmailData)
    {
        $oEmail = oxNew(Email::class);

        $bEmailSent = $oEmail->sendSupportEmail($aEmailData);
        $this->_aViewData += [
            'alertMessage' => $bEmailSent ?
                Helper::translate('wdpg_success_email') : Helper::translate('wdpg_support_send_error'),
            'alertType' => $bEmailSent ? 'success' : 'error',
            'replyToEmail' => '',
            'fromEmail' => '',
            'body' => '',
        ];
    }

    /**
     * Adds data received from form into the supplied array
     *
     * @param array $aEmailData
     *
     * @since 1.0.0
     */
    protected function _addDataFromForm(&$aEmailData)
    {
        $body = $this->getConfig()->getRequestParameter('module_support_text');
        $sFromEmail = $this->getConfig()->getRequestParameter('module_support_email_from');
        $sReplyToEmail = $this->getConfig()->getRequestParameter('module_support_email_reply');

        $aEmailData = array_merge($aEmailData, [
            'body' => $body,
            'replyTo' => $sReplyToEmail,
            'from' => $sFromEmail
        ]);
    }

    /**
     * Adds automatically provided data into the supplied array
     *
     * @param array $aEmailData email data array
     *
     * @since 1.0.0
     */
    protected function _addShopData(&$aEmailData)
    {
        $aEmailData = array_merge($aEmailData, [
            'modules' => $this->_getOtherModules(),
            'module' => $this->_getModule(),
            'shopVersion' => $this->getConfig()->getVersion(),
            'shopEdition' => $this->getConfig()->getFullEdition(),
            'phpVersion' => phpversion(),
            'system' => php_uname(),
            'subject' => Helper::translate('wdpg_support_email_subject'),
            'recipient' => $this->_getModule()->getInfo('email'),
            'payments' => Helper::getModulePaymentsIncludingInactive()
        ]);
    }

    /**
     * Loads the payment gateway module from database and returns it
     *
     * @return OxidEsales\Eshop\Core\Module\Module
     *
     * @since 1.0.0
     */
    protected function _getModule()
    {
        if (empty($this->_oModule)) {
            $sModuleId = $this->getEditObjectId();
            $this->_oModule = oxNew(Module::class);
            if ($sModuleId) {
                $this->_oModule->load($sModuleId);
            }
        }

        return $this->_oModule;
    }

    /**
     * Returns the list of other modules (without the current one)
     * @return array
     *
     * @since 1.0.0
     */
    protected function _getOtherModules()
    {
        return array_filter(Helper::getModulesList(), function ($module) {
            return $module->getId() !== $this->_getModule()->getId();
        });
    }

    /**
     * Validates the current request.
     *
     * @throws Exception Throws exception if some request params are invalid
     *
     * @since 1.0.0
     */
    private function _validateRequest()
    {
        $sBody = $this->getConfig()->getRequestParameter('module_support_text');
        $sFromEmail = $this->getConfig()->getRequestParameter('module_support_email_from');
        $sReplyToEmail = $this->getConfig()->getRequestParameter('module_support_email_reply');

        $this->_aViewData['replyToEmail'] = $sReplyToEmail;
        $this->_aViewData['fromEmail'] = $sFromEmail;
        $this->_aViewData['body'] = $sBody;

        if (empty($sFromEmail) || !Helper::isEmailValid($sFromEmail)) {
            throw new Exception(Helper::translate('wdpg_enter_valid_email_error'));
        }

        if ($sReplyToEmail && !Helper::isEmailValid($sReplyToEmail)) {
            throw new Exception(Helper::translate('wdpg_enter_valid_email_error'));
        }

        if (empty($sBody)) {
            throw new Exception(Helper::translate('wdpg_message_empty_error'));
        }
    }
}
