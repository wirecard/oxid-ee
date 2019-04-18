<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use Wirecard\Oxid\Core\Helper;

use Wirecard\PaymentSdk\Config\Config as PaymentSdkConfig;
use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;

/**
 * Class CreditCardPaymentMethod
 *
 */
class CreditCardPaymentMethod extends PaymentMethod
{
    /**
     * @inheritdoc
     */
    protected static $_sName = 'creditcard';

    /**
     * @inheritdoc
     *
     * @return CreditCardTransaction
     */
    public function getTransaction()
    {
        return new CreditCardTransaction();
    }

    /**
     * @inheritdoc
     *
     * @param Payment $oPayment
     *
     * @return PaymentSdkConfig
     */
    public function getConfig($oPayment)
    {
        $oConfig = parent::getConfig($oPayment);

        $oCreditCardConfig = new CreditCardConfig();

        if (!empty($oPayment->oxpayments__wdoxidee_maid->value)) {
            $oCreditCardConfig->setNonThreeDCredentials(
                $oPayment->oxpayments__wdoxidee_maid->value,
                $oPayment->oxpayments__wdoxidee_secret->value
            );
        }

        if (!empty($oPayment->oxpayments__wdoxidee_three_d_maid->value)) {
            $oCreditCardConfig->setThreeDCredentials(
                $oPayment->oxpayments__wdoxidee_three_d_maid->value,
                $oPayment->oxpayments__wdoxidee_three_d_secret->value
            );
        }

        $this->_addThreeDLimits($oPayment, $oCreditCardConfig);

        $oConfig->add($oCreditCardConfig);

        return $oConfig;
    }

    /**
     * @param Payment          $oPayment
     * @param CreditCardConfig $oCreditCardConfig
     */
    private function _addThreeDLimits($oPayment, &$oCreditCardConfig)
    {
        /**
         * @var $oShopConfig Config
         */
        $oShopConfig = Registry::getConfig();

        $oThreeDCurrency = $oShopConfig->getCurrencyObject($oPayment->oxpayments__wdoxidee_limits_currency->value);
        $oShopCurrency = $oShopConfig->getActShopCurrencyObject();

        if ($oPayment->oxpayments__wdoxidee_non_three_d_max_limit->value !== '') {
            $oCreditCardConfig->addNonThreeDMaxLimit(new Amount(
                $this->_convertAmountCurrency(
                    $oPayment->oxpayments__wdoxidee_non_three_d_max_limit->value,
                    $oThreeDCurrency->rate,
                    $oShopCurrency
                ),
                $oShopCurrency->name
            ));
        }

        if ($oPayment->oxpayments__wdoxidee_three_d_min_limit->value !== '') {
            $oCreditCardConfig->addThreeDMinLimit(new Amount(
                $this->_convertAmountCurrency(
                    $oPayment->oxpayments__wdoxidee_three_d_min_limit->value,
                    $oThreeDCurrency->rate,
                    $oShopCurrency
                ),
                $oShopCurrency->name
            ));
        }
    }

    /**
     * @param float  $fAmount
     * @param float  $fFromFactor
     * @param object $oToCurrency
     *
     * @return float
     */
    private function _convertAmountCurrency($fAmount, $fFromFactor, $oToCurrency)
    {
        return Registry::getUtils()->fround($fAmount / $fFromFactor * $oToCurrency->rate, $oToCurrency);
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getConfigFields()
    {
        $parentConfigFields = parent::getConfigFields();
        $additionalFields = [
            'threeDMaid' => [
                'type' => 'text',
                'field' => 'oxpayments__wdoxidee_three_d_maid',
                'title' => Helper::translate('config_three_d_merchant_account_id'),
                'description' => Helper::translate('config_three_d_merchant_account_id_desc'),
            ],
            'threeDSecret' => [
                'type' => 'text',
                'field' => 'oxpayments__wdoxidee_three_d_secret',
                'title' => Helper::translate('config_three_d_merchant_secret'),
                'description' => Helper::translate('config_three_d_merchant_secret_desc'),
            ],
            'nonThreeDMaxLimit' => [
                'type' => 'text',
                'field' => 'oxpayments__wdoxidee_non_three_d_max_limit',
                'title' => Helper::translate('config_ssl_max_limit'),
                'description' => Helper::translate('config_ssl_max_limit_desc'),
            ],
            'threeDMinLimit' => [
                'type' => 'text',
                'field' => 'oxpayments__wdoxidee_three_d_min_limit',
                'title' => Helper::translate('config_three_d_min_limit'),
                'description' => Helper::translate('config_three_d_min_limit_desc'),
            ],
            'limitsCurrency' => [
                'type' => 'select',
                'field' => 'oxpayments__wdoxidee_limits_currency',
                'options' => $this->_getCurrencyOptions(),
                'title' => Helper::translate('default_currency'),
            ],
            'moreInfo' => [
                'type' => 'link',
                'title' => Helper::translate('more_info'),
                'link' => 'https://github.com/wirecard/oxid-ee/wiki/Credit-Card#non-3-d-secure-and-3-d-secure-limits',
                'text' => Helper::translate('three_d_link_text')
            ],
        ];

        return array_merge($parentConfigFields, $additionalFields);
    }

    /**
     * Return array for currency select options
     *
     * @return array
     */
    private function _getCurrencyOptions()
    {
        $aCurrencies = Registry::getConfig()->getCurrencyArray();
        $aOptions = [];

        foreach ($aCurrencies as $oCurrency) {
            $aOptions[$oCurrency->name] = $oCurrency->name;
        }

        return $aOptions;
    }
}
