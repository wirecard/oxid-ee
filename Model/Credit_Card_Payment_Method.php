<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use \OxidEsales\EshopCommunity\Core\Config;
use \OxidEsales\Eshop\Application\Model\Payment;
use \Wirecard\PaymentSdk\Config\Config as Wirecard_Config;
use \Wirecard\PaymentSdk\Config\CreditCardConfig;
use \Wirecard\PaymentSdk\Entity\Amount;
use \Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use \Wirecard\PaymentSdk\Transaction\Transaction;

class Credit_Card_Payment_Method extends Payment_Method
{
    const NAME = "wdcreditcard";

    /**
     * @inheritdoc
     */
    public function getTransaction(): Transaction
    {
        return new CreditCardTransaction();
    }

    /**
     * @inheritdoc
     */
    public function getConfig(Payment $oPayment): Wirecard_Config
    {
        $oConfig = parent::getConfig($oPayment);

        $oCreditCardConfig = new CreditCardConfig();

        if (!is_null($oPayment->oxpayments__wdoxidee_maid->value)) {
            $oCreditCardConfig->setNonThreeDCredentials(
                $oPayment->oxpayments__wdoxidee_maid->value,
                $oPayment->oxpayments__wdoxidee_secret->value
            );
        }

        if (!is_null($oPayment->oxpayments__wdoxidee_maid->value)) {
            $oCreditCardConfig->setThreeDCredentials(
                $oPayment->oxpayments__wdoxidee_maid->value,
                $oPayment->oxpayments__wdoxidee_three_d_secret->value
            );
        }

        /**
         * @var $oShopConfig Config
         */
        $oShopConfig = oxNew(Config::class);

        $oThreeDCurrency = $oShopConfig->getCurrencyObject($oPayment->oxpayments__wdoxidee_default_currency);
        $oShopCurrency = $oShopConfig->getActShopCurrencyObject();

        if ($oPayment->oxpayments__wdoxidee_non_three_d_max_limit->value !== '') {
            $oCreditCardConfig->addNonThreeDMaxLimit(new Amount(
                $this->_convertAmountCurrency(
                    $oPayment->oxpayments__wdoxidee_non_three_d_max_limit->value,
                    $oThreeDCurrency->rate,
                    $oShopCurrency->rate
                ),
                $oShopCurrency->name
            ));
        }

        //FIXME cgrach: convert to current shop currency
        // see /Core/Config.php::getCurrencyArray
        if ($oPayment->oxpayments__wdoxidee_three_d_min_limit->value !== '') {
            $oCreditCardConfig->addThreeDMinLimit(new Amount(
                $this->_convertAmountCurrency(
                    $oPayment->oxpayments__wdoxidee_three_d_min_limit->value,
                    $oThreeDCurrency->rate,
                    $oShopCurrency->rate
                ),
                $oShopCurrency->name
            ));
        }

        $oConfig->add($oCreditCardConfig);

        return $oConfig;
    }

    private function _convertAmountCurrency(float $fAmount, float $fFromFactor, float $fToFactor)
    {
        return $fAmount / $fFromFactor * $fToFactor;
    }
}
