<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use \Wirecard\Oxid\Extend\Order;
use \Wirecard\PaymentSdk\Config\Config;
use \Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use \Wirecard\PaymentSdk\Transaction\Transaction;
use \Wirecard\PaymentSdk\Transaction\PayPalTransaction;

use \OxidEsales\Eshop\Application\Model\Payment;

/**
 * Payment method implementation for Paypal
 */
class Paypal_Payment_Method extends Payment_Method
{
    /**
     * @inheritdoc
     */
    protected static $_sName = "paypal";

    /**
     * Get the payment method's configuration
     *
     * @return Config
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function getConfig(): Config
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->load(self::getName(true));
        $oConfig = new Config(
            $oPayment->oxpayments__wdoxidee_apiurl->value,
            $oPayment->oxpayments__wdoxidee_httpuser->value,
            $oPayment->oxpayments__wdoxidee_httppass->value
        );
        $oPaymentMethodConfig = new PaymentMethodConfig(
            self::getName(),
            $oPayment->oxpayments__wdoxidee_maid->value,
            $oPayment->oxpayments__wdoxidee_secret->value
        );
        $oConfig->add($oPaymentMethodConfig);

        return $oConfig;
    }

    /**
     * Get the current transaction to be processed
     *
     * @var double $dAmount
     * @var Order $oOrder
     *
     * @return Transaction
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function getTransaction(): Transaction
    {
        return new PayPalTransaction();
    }
}
