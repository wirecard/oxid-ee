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

use \OxidEsales\Eshop\Core\Registry;

/**
 * Payment method implementation for Paypal
 */
class Paypal_Payment_Method extends Payment_Method
{
    const NAME = "wdpaypal";

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $oLogger;

    /**
     * Paypal_Payment_Method constructor.
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function __construct()
    {
        $this->oLogger = Registry::getLogger();
    }

    /**
     * Get the payment method's configuration
     *
     * @return Config
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function getConfig(): Config
    {
        $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $payment->load(self::NAME);
        $config = new Config(
            $payment->oxpayments__wdoxidee_apiurl->value,
            $payment->oxpayments__wdoxidee_httpuser->value,
            $payment->oxpayments__wdoxidee_httppass->value
        );
        $oPaymentMethodConfig = new PaymentMethodConfig(
            PayPalTransaction::NAME,
            $payment->oxpayments__wdoxidee_maid->value,
            $payment->oxpayments__wdoxidee_secret->value
        );
        $config->add($oPaymentMethodConfig);

        return $config;
    }

    /**
     * Get the current transaction to be processed
     *
     * @var double $dAmount
     * @var Order $oOrder
     *
     * @return \Wirecard\PaymentSdk\Transaction\Transaction
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function getTransaction(): Transaction
    {
        $oTransaction = new PayPalTransaction();
        return $oTransaction;
    }
}
