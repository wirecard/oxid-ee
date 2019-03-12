<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use Wirecard\Oxid\Model\Payment_Method;
use Wirecard\Oxid\Model\Paypal_Payment_Method;

/**
 * Class Payment_Method_Factory
 *
 * @package Wirecard\Core
 *
 */
class Payment_Method_Factory
{
    /**
     * Create a Wirecard payment method
     *
     * @param string $sPaymentMethodType
     * @return Payment_Method
     * @throws \Exception if $sPaymentMethodType is not registered
     */
    public static function create(string $sPaymentMethodType): Payment_Method
    {
        switch ($sPaymentMethodType) {
            case Paypal_Payment_Method::NAME:
                return new Paypal_Payment_Method();
            default:
                throw new \Exception("payment type not registered: $sPaymentMethodType");
        }
    }
}
