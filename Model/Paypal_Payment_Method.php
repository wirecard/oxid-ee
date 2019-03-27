<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use \OxidEsales\Eshop\Application\Model\Payment;
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
     * @inheritdoc
     *
     * @param Payment $oPayment
     *
     * @return Config
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function getConfig(Payment $oPayment): Config
    {
        $oConfig = parent::getConfig($oPayment);

        $oPaymentMethodConfig = new PaymentMethodConfig(
            PayPalTransaction::NAME,
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
