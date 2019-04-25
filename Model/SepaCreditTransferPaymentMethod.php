<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use Wirecard\Oxid\Core\PaymentMethodHelper;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\SepaConfig;
use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;

/**
 * Payment method implementation for Sepa Credit Transfer.
 *
 * @since 1.0.1
 */
class SepaCreditTransferPaymentMethod extends PaymentMethod
{
    /**
     * @inheritdoc
     *
     * @since 1.0.1
     */
    protected static $_sName = "sepacredit";

    /**
     * @var bool
     *
     * @since 1.0.1
     */
    protected static $_bMerchantOnly = true;

    /**
     * @inheritdoc
     *
     * @return Config
     *
     * @since 1.0.1
     */
    public function getConfig()
    {
        $oConfig = parent::getConfig();

        $oPaymentMethodConfig = new SepaConfig(
            SepaCreditTransferTransaction::NAME,
            $this->_oPayment->oxpayments__wdoxidee_maid->value,
            $this->_oPayment->oxpayments__wdoxidee_secret->value
        );

        $oConfig->add($oPaymentMethodConfig);

        return $oConfig;
    }

    /**
     * @inheritdoc
     *
     * @return SepaCreditTransferTransaction
     *
     * @since 1.0.1
     */
    public function getTransaction()
    {
        return new SepaCreditTransferTransaction();
    }

    /**
     * Adds all needed data to the post-processing transaction
     *
     * @param SepaCreditTransferTransaction $oTransaction
     * @param Transaction                   $oParentTransaction
     *
     * @since 1.0.1
     */
    public function addPostProcessingTransactionData(&$oTransaction, $oParentTransaction)
    {
        $oMandate = PaymentMethodHelper::getMandate(
            $oParentTransaction->wdoxidee_ordertransactions__orderid->value
        );

        $oTransaction->setMandate($oMandate);
    }
}
