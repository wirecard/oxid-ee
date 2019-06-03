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
 * Payment method implementation for SEPA Credit Transfer.
 *
 * @since 1.1.0
 */
class SepaCreditTransferPaymentMethod extends PaymentMethod
{
    /**
     * @inheritdoc
     *
     * @since 1.1.0
     */
    protected static $_sName = "sepacredit";

    /**
     * @inheritdoc
     *
     * @var bool
     *
     * @since 1.1.0
     */
    protected static $_bMerchantOnly = true;

    /**
     * @inheritdoc
     *
     * @return Config
     *
     * @since 1.1.0
     */
    public function getConfig()
    {
        $oConfig = parent::getConfig();

        $oCtPayment = PaymentMethodHelper::getPaymentById(self::getOxidFromSDKName(self::$_sName));
        $oPaymentMethodConfig = new SepaConfig(
            SepaCreditTransferTransaction::NAME,
            $oCtPayment->oxpayments__wdoxidee_maid->value,
            $oCtPayment->oxpayments__wdoxidee_secret->value
        );

        $oConfig->add($oPaymentMethodConfig);

        return $oConfig;
    }

    /**
     * @inheritdoc
     *
     * @return SepaCreditTransferTransaction
     *
     * @since 1.1.0
     */
    public function getTransaction()
    {
        return new SepaCreditTransferTransaction();
    }

    /**
     * @inheritdoc
     *
     * @param string      $sAction
     * @param Transaction $oParentTransaction
     *
     * @return SepaCreditTransferTransaction
     *
     * @since 1.2.0
     */
    public function getPostProcessingTransaction($sAction, $oParentTransaction)
    {
        $oTransaction = new SepaCreditTransferTransaction();

        $oMandate = PaymentMethodHelper::getMandate(
            $oParentTransaction->wdoxidee_ordertransactions__orderid->value
        );
        $oTransaction->setMandate($oMandate);

        return $oTransaction;
    }
}
