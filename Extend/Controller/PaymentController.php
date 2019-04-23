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
    const ERROR_CODE_CANCELED = '-100';
    const ERROR_CODE_FAILED = '-101';

    /**
     * @inheritdoc
     */
    protected function _unsetPaymentErrors()
    {
        $oOrder = oxNew(Order::class);
        $sOrderId = Registry::getSession()->getVariable('sess_challenge');

        if ($oOrder->load($sOrderId)) {
            switch (Registry::getConfig()->getRequestParameter('payerror')) {
                case self::ERROR_CODE_CANCELED:
                    $oOrder->handleOrderState(Order::STATE_CANCELED);
                    break;
                case self::ERROR_CODE_FAILED:
                    $oOrder->handleOrderState(Order::STATE_FAILED);
                    break;
            }
        }

        parent::_unsetPaymentErrors();
    }
}
