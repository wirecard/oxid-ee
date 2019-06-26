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

use Wirecard\Oxid\Model\CreditCardPaymentMethod;
use Wirecard\Oxid\Model\GiropayPaymentMethod;
use Wirecard\Oxid\Model\MasterpassPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod;
use Wirecard\Oxid\Model\PayolutionInvoicePaymentMethod;
use Wirecard\Oxid\Model\PaypalPaymentMethod;
use Wirecard\Oxid\Model\RatepayInvoicePaymentMethod;
use Wirecard\Oxid\Model\SepaCreditTransferPaymentMethod;
use Wirecard\Oxid\Model\SepaDirectDebitPaymentMethod;
use Wirecard\Oxid\Model\SofortPaymentMethod;
use Wirecard\Oxid\Model\IdealPaymentMethod;
use Wirecard\Oxid\Model\EpsPaymentMethod;

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
        $aClasses = [];

        foreach ([
            CreditCardPaymentMethod::class,
            EpsPaymentMethod::class,
            GiropayPaymentMethod::class,
            IdealPaymentMethod::class,
            MasterpassPaymentMethod::class,
            PaypalPaymentMethod::class,
            PayolutionInvoicePaymentMethod::class,
            RatepayInvoicePaymentMethod::class,
            SepaCreditTransferPaymentMethod::class,
            SepaDirectDebitPaymentMethod::class,
            SofortPaymentMethod::class,
        ] as $sClassName) {
            $aClasses[$sClassName::getName(true)] = $sClassName;
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
