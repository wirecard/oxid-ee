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

use Wirecard\Oxid\Extend\Model\Order;

/**
 * Class Order
 *
 * @mixin \OxidEsales\Eshop\Application\Controller\PaymentController
 */
class PaymentController extends PaymentController_parent
{

    /**
     * Handles order on errors
     * @inheritdoc
     */
    protected function _unsetPaymentErrors()
    {
        $oOrder = oxNew(Order::class);
        $sOrderId = Registry::getSession()->getVariable('sess_challenge');
        if ($oOrder->load($sOrderId)) {
            $iPayError = Registry::getConfig()->getRequestParameter('payerror');

            if ($iPayError === '-100') {
                $oOrder->handleCanceledFailed(Order::STATE_CANCELED);
            } elseif ($iPayError === '-101') {
                $oOrder->handleCanceledFailed(Order::STATE_FAILED);
            }
        }

        parent::_unsetPaymentErrors();
    }
}
