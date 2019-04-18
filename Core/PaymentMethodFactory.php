<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use Wirecard\Oxid\Model\CreditCardPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod;
use Wirecard\Oxid\Model\PaypalPaymentMethod;

/**
 * Class PaymentMethodFactory
 *
 * @package Wirecard\Core
 *
 */
class PaymentMethodFactory
{
    /**
     * Create a Wirecard payment method
     *
     * @param string $sPaymentMethodType
     * @return PaymentMethod
     * @throws \Exception if $sPaymentMethodType is not registered
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public static function create(string $sPaymentMethodType): PaymentMethod
    {
        switch ($sPaymentMethodType) {
            case PaypalPaymentMethod::getName(true):
                return new PaypalPaymentMethod();
            case CreditCardPaymentMethod::getName(true):
                return new CreditCardPaymentMethod();
            default:
                throw new \Exception("payment type not registered: $sPaymentMethodType");
        }
    }
}
