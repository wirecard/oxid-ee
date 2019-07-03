<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\PaymentMethodHelper;
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
     * @return PaymentSdkConfig
     *
     * @since 1.0.0
     */
    public function getConfig()
    {
        $oConfig = parent::getConfig();

        $oCreditCardConfig = new CreditCardConfig();

        if (!empty($this->_oPayment->oxpayments__wdoxidee_maid->value)) {
            $oCreditCardConfig->setNonThreeDCredentials(
                $this->_oPayment->oxpayments__wdoxidee_maid->value,
                $this->_oPayment->oxpayments__wdoxidee_secret->value
            );
        }

        if (!empty($this->_oPayment->oxpayments__wdoxidee_three_d_maid->value)) {
            $oCreditCardConfig->setThreeDCredentials(
                $this->_oPayment->oxpayments__wdoxidee_three_d_maid->value,
                $this->_oPayment->oxpayments__wdoxidee_three_d_secret->value
            );
        }

        $this->_addThreeDLimits($oCreditCardConfig);

        $oConfig->add($oCreditCardConfig);

        return $oConfig;
    }

    /**
     * @param CreditCardConfig $oCreditCardConfig
     *
     * @since 1.0.0
     */
    private function _addThreeDLimits(&$oCreditCardConfig)
    {
        /**
         * @var $oShopConfig Config
         */
        $oShopConfig = Registry::getConfig();

        $oThreeDCurrency =
            $oShopConfig->getCurrencyObject($this->_oPayment->oxpayments__wdoxidee_limits_currency->value);
        $oShopCurrency = $oShopConfig->getActShopCurrencyObject();

        if ($this->_oPayment->oxpayments__wdoxidee_non_three_d_max_limit->value !== '') {
            $oCreditCardConfig->addNonThreeDMaxLimit(new Amount(
                $this->_convertAmountCurrency(
                    $this->_oPayment->oxpayments__wdoxidee_non_three_d_max_limit->value,
                    $oThreeDCurrency->rate,
                    $oShopCurrency
                ),
                $oShopCurrency->name
            ));
        }

        if ($this->_oPayment->oxpayments__wdoxidee_three_d_min_limit->value !== '') {
            $oCreditCardConfig->addThreeDMinLimit(new Amount(
                $this->_convertAmountCurrency(
                    $this->_oPayment->oxpayments__wdoxidee_three_d_min_limit->value,
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
        $fDivisor = $fFromFactor * $oToCurrency->rate;
        if ($fDivisor === 0.0) {
            return 0.0;
        }

        return Registry::getUtils()->fround($fAmount / $fDivisor, $oToCurrency);
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
        $iUrlFieldsOffset = 1;
        $aParentFields = parent::getConfigFields();

        $aFirstFields = array_slice($aParentFields, 0, $iUrlFieldsOffset, true);
        $aFirstFields = array_merge($aFirstFields, [
            'apiUrlWpp' => [
                'type' => 'text',
                'field' => 'oxpayments__apiurl_wpp',
                'title' => Helper::translate('wd_config_wpp_url'),
                'description' => Helper::translate('wd_config_wpp_url_desc'),
            ],
        ]);
        $aFirstFields = array_merge(
            $aFirstFields,
            array_slice($aParentFields, $iUrlFieldsOffset, count($aParentFields) - 1, true)
        );

        $aAdditionalFields = [
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
                'options' => PaymentMethodHelper::getCurrencyOptions(),
                'title' => Helper::translate('wd_default_currency'),
            ],
            'moreInfo' => [
                'type' => 'link',
                'title' => Helper::translate('wd_more_info'),
                'link' => 'https://github.com/wirecard/oxid-ee/wiki/Credit-Card#non-3-d-secure-and-3-d-secure-limits',
                'text' => Helper::translate('wd_three_d_link_text'),
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

        return array_merge($aFirstFields, $aAdditionalFields);
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
                'apiUrlWpp',
            ]
        );
    }

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.2.0
     */
    public function getMetaDataFieldNames()
    {
        return [
            'apiurl_wpp',
        ];
    }
}
