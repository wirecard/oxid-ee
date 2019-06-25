<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend\Controller;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Model\PaymentInAdvancePaymentInformation;

use OxidEsales\Eshop\Core\Registry;

/**
 * Class ThankYouController
 *
 * @mixin \OxidEsales\Eshop\Application\Controller\ThankYouController
 *
 * @since 1.0.0
 */
class ThankYouController extends ThankYouController_parent
{

     /**
     * @var PaymentInAdvancePaymentInformation
     *
     * @since 1.3.0
     */
    private $_oPaymentInAdvanceInfo;

    /**
     * Extends the parent init method
     * deletes a wdtoken and updates the order number in the transaction table
     *
     * @since 1.0.0
     */
    public function init()
    {
        $oSession = Registry::getSession();
        $oSession->deleteVariable("wdtoken");

        Helper::addToViewData($this, [
            'sendPendingEmailsSettings' => $this->getConfig()->getConfigParam('wd_email_on_pending_orders'),
        ]);

        $this->_oPaymentInAdvanceInfo = $oSession->getVariable('wdPaymentInAdvancePaymentInformation');

        if ($this->_oPaymentInAdvanceInfo) {
            $oSession->deleteVariable("wdPaymentInAdvancePaymentInformation");
        }

        parent::init();
    }

    /**
     * Getter for _oPaymentInAdvanceInfo (amount, IBAN, BIC, Provider Transaction Reference ID)
     *
     * @since 1.3.0
     */
    public function getPaymentInAdvanceInfo() {
        return $this->_oPaymentInAdvanceInfo;
    }
}
