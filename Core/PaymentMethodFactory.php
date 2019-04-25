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
use Wirecard\Oxid\Model\SepaCreditTransferPaymentMethod;
use Wirecard\Oxid\Model\SofortPaymentMethod;

use OxidEsales\Eshop\Core\Exception\StandardException;

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
     * @throws StandardException if $sPaymentMethodType is not registered
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
            case SepaCreditTransferPaymentMethod::getName(true):
                return new SepaCreditTransferPaymentMethod();
            default:
                throw new StandardException("payment type not registered: $sPaymentMethodType");
        }
    }
}
