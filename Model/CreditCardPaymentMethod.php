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
use Wirecard\Oxid\Model\Transaction as TransactionModel;

use Wirecard\PaymentSdk\Config\Config as PaymentSdkConfig;
use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;

/**
 * Class CreditCardPaymentMethod
 *
 * @since 1.0.0
 */
class CreditCardPaymentMethod extends PaymentMethod
{
    /**
     * @inheritdoc
     *
     * @since 1.0.0
     */
    protected static $_sName = 'creditcard';

    /**
     * @inheritdoc
     *
     * @return CreditCardTransaction
     *
     * @since 1.0.0
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
     *
     * @since 1.0.0
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
     *
     * @since 1.0.0
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
     *
     * @since 1.0.0
     */
    private function _convertAmountCurrency($fAmount, $fFromFactor, $oToCurrency)
    {
        return Registry::getUtils()->fround($fAmount / $fFromFactor * $oToCurrency->rate, $oToCurrency);
    }

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getConfigFields()
    {
        $parentConfigFields = parent::getConfigFields();
        $additionalFields = [
            'threeDMaid' => [
                'type' => 'text',
                'field' => 'oxpayments__wdoxidee_three_d_maid',
                'title' => Helper::translate('wd_config_three_d_merchant_account_id'),
                'description' => Helper::translate('wd_config_three_d_merchant_account_id_desc'),
            ],
            'threeDSecret' => [
                'type' => 'text',
                'field' => 'oxpayments__wdoxidee_three_d_secret',
                'title' => Helper::translate('wd_config_three_d_merchant_secret'),
                'description' => Helper::translate('wd_config_three_d_merchant_secret_desc'),
            ],
            'threeDMinLimit' => [
                'type' => 'text',
                'field' => 'oxpayments__wdoxidee_three_d_min_limit',
                'title' => Helper::translate('wd_config_three_d_min_limit'),
                'description' => Helper::translate('wd_config_three_d_min_limit_desc'),
            ],
            'nonThreeDMaxLimit' => [
                'type' => 'text',
                'field' => 'oxpayments__wdoxidee_non_three_d_max_limit',
                'title' => Helper::translate('wd_config_ssl_max_limit'),
                'description' => Helper::translate('wd_config_ssl_max_limit_desc'),
            ],
            'limitsCurrency' => [
                'type' => 'select',
                'field' => 'oxpayments__wdoxidee_limits_currency',
                'options' => $this->_getCurrencyOptions(),
                'title' => Helper::translate('wd_default_currency'),
            ],
            'moreInfo' => [
                'type' => 'link',
                'title' => Helper::translate('wd_more_info'),
                'link' => 'https://github.com/wirecard/oxid-ee/wiki/Credit-Card#non-3-d-secure-and-3-d-secure-limits',
                'text' => Helper::translate('wd_three_d_link_text')
            ],
            'descriptor' => [
                'type'        => 'select',
                'field'       => 'oxpayments__wdoxidee_descriptor',
                'options'     => [
                    '1'       => Helper::translate('wd_yes'),
                    '0'       => Helper::translate('wd_no'),
                ],
                'title'       => Helper::translate('wd_config_descriptor'),
                'description' => Helper::translate('wd_config_descriptor_desc'),
            ],
            'additionalInfo' => [
                'type'        => 'select',
                'field'       => 'oxpayments__wdoxidee_additional_info',
                'options'     => [
                    '1'       => Helper::translate('wd_yes'),
                    '0'       => Helper::translate('wd_no'),
                ],
                'title'       => Helper::translate('wd_config_additional_info'),
                'description' => Helper::translate('wd_config_additional_info_desc'),
            ],
            'deleteCanceledOrder' => [
                'type' => 'select',
                'field' => 'oxpayments__wdoxidee_delete_canceled_order',
                'options' => [
                    '1' => Helper::translate('wd_yes'),
                    '0' => Helper::translate('wd_no'),
                ],
                'title' => Helper::translate('wd_config_delete_cancel_order'),
                'description' => Helper::translate('wd_config_delete_cancel_order_desc'),
            ],
            'deleteFailedOrder' => [
                'type' => 'select',
                'field' => 'oxpayments__wdoxidee_delete_failed_order',
                'options' => [
                    '1' => Helper::translate('wd_yes'),
                    '0' => Helper::translate('wd_no'),
                ],
                'title' => Helper::translate('wd_config_delete_failure_order'),
                'description' => Helper::translate('wd_config_delete_failure_order_desc'),
            ],
            'paymentAction' => [
                'type'        => 'select',
                'field'       => 'oxpayments__wdoxidee_transactionaction',
                'options'     => TransactionModel::getTranslatedActions(),
                'title'       => Helper::translate('wd_config_payment_action'),
                'description' => Helper::translate('wd_config_payment_action_desc'),
            ],
        ];

        return array_merge($parentConfigFields, $additionalFields);
    }

    /**
     * Return array for currency select options
     *
     * @return array
     *
     * @since 1.0.0
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

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getPublicFieldNames()
    {
        return array_merge(
            parent::getPublicFieldNames(),
            [
                'threeDMaid',
                'nonThreeDMaxLimit',
                'threeDMinLimit',
                'limitsCurrency',
                'descriptor',
                'additionalInfo',
                'paymentAction',
                'deleteCanceledOrder',
                'deleteFailedOrder',
            ]
        );
    }
}
