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
use OxidEsales\EshopCommunity\Core\Registry;
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

        $oCreditCardConfig = new CreditCardConfig(
            '53f2895a-e4de-4e82-a813-0d87a10e55e6',//$oPayment->oxpayments__wdoxidee_maid->value,
            'dbc5a498-9a66-43b9-bf1d-a618dd399684'//$oPayment->oxpayments__wdoxidee_secret->value
        );

        if (!is_null($oPayment->oxpayments__wdoxidee_maid->value)) {
            $oCreditCardConfig->setSSLCredentials(
                '53f2895a-e4de-4e82-a813-0d87a10e55e6', //$oPayment->oxpayments__wdoxidee_maid->value,
                'dbc5a498-9a66-43b9-bf1d-a618dd399684'//$oPayment->oxpayments__wdoxidee_secret->value
            );
        }

        if (!is_null($oPayment->oxpayments__wdoxidee_maid->value)) {
            $oCreditCardConfig->setThreeDCredentials(
                '508b8896-b37d-4614-845c-26bf8bf2c948',//$oPayment->oxpayments__wdoxidee_maid->value,
                'bc5a498-9a66-43b9-bf1d-a618dd399684'//$oPayment->oxpayments__wdoxidee_three_d_secret->value
            );
        }

        /**
         * @var $oShopConfig Config
         */
        $oShopConfig = oxNew(Config::class);

        $oThreeDCurrency = $oShopConfig->getCurrencyObject($oPayment->oxpayments__wdoxidee_default_currency);
        $oShopCurrency = $oShopConfig->getActShopCurrencyObject();

        if ($oPayment->oxpayments__wdoxidee_non_three_d_max_limit->value !== '') {
            $oCreditCardConfig->addSslMaxLimit(new Amount(
                300,
                'EUR'
//                $this->_convertAmountCurrency(
//                    $oPayment->oxpayments__wdoxidee_non_three_d_max_limit->value,
//                    $oThreeDCurrency->rate,
//                    $oShopCurrency->rate
//                ),
//                $oShopCurrency->name
            ));
        }

        if ($oPayment->oxpayments__wdoxidee_three_d_min_limit->value !== '') {
            $oCreditCardConfig->addThreeDMinLimit(new Amount(
                100,
                'EUR'
//                $this->_convertAmountCurrency(
//                    $oPayment->oxpayments__wdoxidee_three_d_min_limit->value,
//                    $oThreeDCurrency->rate,
//                    $oShopCurrency->rate
//                ),
//                $oShopCurrency->name
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
