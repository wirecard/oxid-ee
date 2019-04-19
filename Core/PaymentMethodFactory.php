<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use Exception;

use Wirecard\Oxid\Model\CreditCardPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod;
use Wirecard\Oxid\Model\PaypalPaymentMethod;
use Wirecard\Oxid\Model\SofortPaymentMethod;

/**
 * Class PaymentMethodFactory
 *
 * @package Wirecard\Core
 *
 * @since 1.0.0
 */
class PaymentMethodFactory
{
    /**
     * Create a payment method
     *
     * @param string $sPaymentMethodType
     *
     * @return PaymentMethod
     * @throws Exception if $sPaymentMethodType is not registered
     *
     * @since 1.0.0
     */
    public static function create($sPaymentMethodType)
    {
        switch ($sPaymentMethodType) {
            case PaypalPaymentMethod::getName(true):
                return new PaypalPaymentMethod();
            case CreditCardPaymentMethod::getName(true):
                return new CreditCardPaymentMethod();
            case SofortPaymentMethod::getName(true):
                return new SofortPaymentMethod();
            default:
                throw new Exception("payment type not registered: $sPaymentMethodType");
        }
    }
}
