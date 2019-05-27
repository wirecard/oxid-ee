<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use OxidEsales\Eshop\Core\Exception\StandardException;

use Wirecard\Oxid\Model\CreditCardPaymentMethod;
use Wirecard\Oxid\Model\GiropayPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod;
use Wirecard\Oxid\Model\PaypalPaymentMethod;
use Wirecard\Oxid\Model\RatepayInvoicePaymentMethod;
use Wirecard\Oxid\Model\SepaCreditTransferPaymentMethod;
use Wirecard\Oxid\Model\SepaDirectDebitPaymentMethod;
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
     * Returns an array of payment method class names.
     *
     * @return array
     *
     * @since 1.2.0
     */
    public static function getPaymentMethodClasses()
    {
        return [
            CreditCardPaymentMethod::class,
            GiropayPaymentMethod::class,
            PaypalPaymentMethod::class,
            RatepayInvoicePaymentMethod::class,
            SepaCreditTransferPaymentMethod::class,
            SepaDirectDebitPaymentMethod::class,
            SofortPaymentMethod::class,
        ];
    }

    /**
     * Create a payment method
     *
     * @param string $sPaymentMethodType
     *
     * @return PaymentMethod
     * @throws StandardException if $sPaymentMethodType is not registered
     *
     * @since 1.0.0
     */
    public static function create($sPaymentMethodType)
    {
        foreach (self::getPaymentMethodClasses() as $sClassName) {
            if ($sPaymentMethodType === $sClassName::getName(true)) {
                return new $sClassName();
            }
        }

        throw new StandardException("payment type not registered: {$sPaymentMethodType}");
    }
}
