<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use OxidEsales\Eshop\Core\Exception\SystemComponentException;

use Wirecard\Oxid\Model\PaymentMethod\AlipayCrossBorderPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\CreditCardPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\EpsPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\GiropayPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\IdealPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\PaymentInAdvancePaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\PaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\PaymentOnInvoicePaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\PayolutionBtwobPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\PayolutionInvoicePaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\PaypalPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\RatepayInvoicePaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\SepaCreditTransferPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\SepaDirectDebitPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\SofortPaymentMethod;

/**
 * Class PaymentMethodFactory
 *
 * @package Wirecard\Core
 *
 * @since   1.0.0
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
        $aClasses = [];

        foreach ([
            AlipayCrossBorderPaymentMethod::class,
            CreditCardPaymentMethod::class,
            EpsPaymentMethod::class,
            GiropayPaymentMethod::class,
            IdealPaymentMethod::class,
            PaymentOnInvoicePaymentMethod::class,
            PaymentInAdvancePaymentMethod::class,
            PaypalPaymentMethod::class,
            PayolutionInvoicePaymentMethod::class,
            PayolutionBtwobPaymentMethod::class,
            RatepayInvoicePaymentMethod::class,
            SepaCreditTransferPaymentMethod::class,
            SepaDirectDebitPaymentMethod::class,
            SofortPaymentMethod::class,
        ] as $sClassName) {
            $aClasses[$sClassName::getName()] = $sClassName;
        }

        return $aClasses;
    }

    /**
     * Create a payment method
     *
     * @param string $sPaymentMethodType
     *
     * @return PaymentMethod
     * @throws SystemComponentException if $sPaymentMethodType is not registered
     *
     * @since 1.0.0
     */
    public static function create($sPaymentMethodType)
    {
        $aClasses = self::getPaymentMethodClasses();

        if (isset($aClasses[$sPaymentMethodType])) {
            return new $aClasses[$sPaymentMethodType];
        }

        throw new SystemComponentException("payment type not registered: {$sPaymentMethodType}");
    }
}
