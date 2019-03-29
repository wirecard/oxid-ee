<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace Wirecard\Oxid\Model;

use \Wirecard\Oxid\Extend\Order;
use \Wirecard\PaymentSdk\Config\Config;
use \Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use \Wirecard\PaymentSdk\Transaction\Transaction;
use \Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use \Wirecard\PaymentSdk\Entity\AccountHolder;
use \Wirecard\PaymentSdk\Entity\Address;

use \OxidEsales\Eshop\Core\Registry;

class Paypal_Payment_Method extends Payment_Method
{
    const NAME = 'paypal';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $oLogger;

    /**
     * Paypal_Payment_Method constructor.
     */
    public function __construct()
    {
        $this->oLogger = Registry::getLogger();
    }

    /**
     * Get the payment method's configuration
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $payment->load(self::NAME);

        $config = new Config($payment->oxpayments__wdoxidee_apiurl->value, $payment->oxpayments__wdoxidee_httpuser->value, $payment->oxpayments__wdoxidee_httppass->value);
        $oPaymentMethodConfig = new PaymentMethodConfig(PayPalTransaction::NAME, $payment->oxpayments__wdoxidee_maid->value, $payment->oxpayments__wdoxidee_secret->value);
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
     */
    public function getTransaction(): Transaction
    {
        $oTransaction = new PayPalTransaction();

        //FIXME cgrach: set details
        $oTransaction->setOrderDetail("details");

        return $oTransaction;
    }
}
