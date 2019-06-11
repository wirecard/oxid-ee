<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend\Controller;

use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Core\SessionHelper;
use Wirecard\Oxid\Extend\Model\Order;
use Wirecard\Oxid\Model\InvoicePaymentMethod;

/**
 * Class PaymentController
 *
 * @mixin \OxidEsales\Eshop\Application\Controller\PaymentController
 *
 * @since 1.0.0
 */
class PaymentController extends PaymentController_parent
{
    const ERROR_CODE_CANCELED = '-100';
    const ERROR_CODE_FAILED = '-101';

    /**
     * @inheritdoc
     *
     * @since 1.0.0
     */
    protected function _unsetPaymentErrors()
    {
        $oOrder = oxNew(Order::class);
        $sOrderId = Registry::getSession()->getVariable('sess_challenge');

        if ($oOrder->load($sOrderId)) {
            switch (Registry::getRequest()->getRequestParameter('payerror')) {
                case self::ERROR_CODE_CANCELED:
                    $oOrder->handleOrderState(Order::STATE_CANCELLED);
                    break;
                case self::ERROR_CODE_FAILED:
                    $oOrder->handleOrderState(Order::STATE_FAILED);
                    break;
            }
        }

        parent::_unsetPaymentErrors();
    }

    /**
     * @inheritdoc
     *
     * @return object
     *
     * @since 1.2.0
     */
    public function getPaymentList()
    {
        return array_filter(parent::getPaymentList(), [PaymentController::class, '_filterPaymentList']);
    }

    /**
     * Filters the payment list for payment methods that should be shown
     *
     * @param object $oPayment
     *
     * @return bool
     *
     * @since 1.2.0
     */
    private function _filterPaymentList($oPayment)
    {
        if (!$oPayment->isCustomPaymentMethod()) {
            return true;
        }

        $oPaymentMethod = $oPayment->getPaymentMethod();
        if ($oPaymentMethod instanceof InvoicePaymentMethod) {
            $oUser = Registry::getSession()->getUser();

            SessionHelper::setDbDateOfBirth($oUser->oxuser__oxbirthdate->value, $oPaymentMethod::getName());
            SessionHelper::setPhone($oUser->oxuser__oxfon->value, $oPaymentMethod::getName());
            SessionHelper::setSaveCheckoutFields(0, $oPaymentMethod::getName());
        }

        return $oPayment->getPaymentMethod()->isPaymentPossible();
    }
}
