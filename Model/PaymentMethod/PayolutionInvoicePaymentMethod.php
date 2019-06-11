<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model\PaymentMethod;

use DateTime;

use Wirecard\Oxid\Core\BasketHelper;
use Wirecard\Oxid\Core\SessionHelper;
use Wirecard\Oxid\Extend\Model\Order;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\PayolutionInvoiceTransaction;

/**
 * Payment method implementation for Payolution Invoice
 *
 * @since 1.2.0
 */
class PayolutionInvoicePaymentMethod extends PayolutionBasePaymentMethod
{
    /**
     * @inheritdoc
     *
     * @since 1.2.0
     */
    protected static $_sName = "payolution-inv";

    /**
     * @inheritdoc
     *
     * @return Config
     *
     * @since 1.2.0
     */
    public function getConfig()
    {
        $oConfig = parent::getConfig();

        // get the currency-specific config values
        $sCurrency = BasketHelper::getCurrencyFromBasket();

        $sMaidField = 'oxpayments__maid_' . $sCurrency;
        $sSecretField = 'oxpayments__secret_' . $sCurrency;

        $oPaymentMethodConfig = new PaymentMethodConfig(
            PayolutionInvoiceTransaction::NAME,
            $this->_oPayment->$sMaidField->value,
            $this->_oPayment->$sSecretField->value
        );

        $oConfig->add($oPaymentMethodConfig);

        return $oConfig;
    }

    /**
     * Get the payments method transaction configuration
     *
     * @return \Wirecard\PaymentSdk\Transaction\Transaction
     *
     * @since 1.2.0
     */
    public function getTransaction()
    {
        return new PayolutionInvoiceTransaction();
    }

    /**
     * @inheritdoc
     *
     * @param PayolutionInvoiceTransaction $oTransaction
     * @param Order                        $oOrder
     *
     * @throws \Exception
     *
     * @since 1.2.0
     */
    public function addMandatoryTransactionData(&$oTransaction, $oOrder)
    {
        $oAccountHolder = $oOrder->getAccountHolder();
        $oAccountHolder->setDateOfBirth(new DateTime(SessionHelper::getDbDateOfBirth(self::getName())));

        if (SessionHelper::isPhoneValid(self::getName())) {
            $oAccountHolder->setPhone(SessionHelper::getPhone(self::getName()));
        }

        $oTransaction->setAccountHolder($oAccountHolder);
    }
}
