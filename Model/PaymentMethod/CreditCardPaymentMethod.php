<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model\PaymentMethod;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\OrderHelper;
use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\Oxid\Core\Vault;
use Wirecard\Oxid\Extend\Model\Order;
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

    const NEW_CARD_TOKEN = '-1';
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
        $iUrlFieldOffset = 1;
        $aFirstFields = parent::getConfigFields();
        Helper::insertToArrayAtPosition(
            $aFirstFields,
            [
                'apiUrlWpp' => [
                    'type' => 'text',
                    'field' => 'oxpayments__apiurl_wpp',
                    'title' => Helper::translate('wd_config_wpp_url'),
                    'description' => Helper::translate('wd_config_wpp_url_desc'),
                ],
            ],
            $iUrlFieldOffset
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
                'type' => 'select',
                'field' => 'oxpayments__wdoxidee_descriptor',
                'options' => [
                    '1' => Helper::translate('wd_yes'),
                    '0' => Helper::translate('wd_no'),
                ],
                'title' => Helper::translate('wd_config_descriptor'),
                'description' => Helper::translate('wd_config_descriptor_desc'),
            ],
            'additionalInfo' => [
                'type' => 'select',
                'field' => 'oxpayments__wdoxidee_additional_info',
                'options' => [
                    '1' => Helper::translate('wd_yes'),
                    '0' => Helper::translate('wd_no'),
                ],
                'title' => Helper::translate('wd_config_additional_info'),
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
                'type' => 'select',
                'field' => 'oxpayments__wdoxidee_transactionaction',
                'options' => TransactionModel::getTranslatedActions(),
                'title' => Helper::translate('wd_config_payment_action'),
                'description' => Helper::translate('wd_config_payment_action_desc'),
            ],
            'oneClickTitle' => [
                'type' => 'separator',
                'title' => Helper::translate('wd_text_vault'),
            ],
            'oneClickEnabled' => [
                'type' => 'select',
                'field' => 'oxpayments__oneclick_enabled',
                'options' => [
                    '1' => Helper::translate('wd_yes'),
                    '0' => Helper::translate('wd_no'),
                ],
                'title' => Helper::translate('wd_config_vault'),
                'description' => Helper::translate('wd_config_vault_desc'),
            ],
            'oneClickChangedShipping' => [
                'type' => 'select',
                'field' => 'oxpayments__oneclick_changed_shipping',
                'options' => [
                    '1' => Helper::translate('wd_yes'),
                    '0' => Helper::translate('wd_no'),
                ],
                'title' => Helper::translate('wd_config_allow_changed_shipping'),
                'description' => Helper::translate('wd_config_allow_changed_shipping_desc'),
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
                'oneClickEnabled',
                'oneClickChangedShipping',
            ]
        );
    }

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.3.0
     */
    public function getMetaDataFieldNames()
    {
        return [
            'apiurl_wpp',
            'oneclick_enabled',
            'oneclick_changed_shipping',
        ];
    }

    /**
     * @inheritdoc
     *
     * @return array
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     *
     * @since 1.3.0
     */
    public function getCheckoutFields()
    {
        $aCards = Vault::getCards();
        if ($aCards &&
            self::_hasShippingAddressChanged() &&
            !$this->_oPayment->oxpayments__oneclick_changed_shipping->value) {
            return [
                [
                    'type' => 'info',
                    'text' => Helper::translate('vault_changed_shipping_text'),
                ],
            ];
        }

        return [
            [
                'type' => 'list',
                'data' => $this->_mapCardsToList($aCards),
            ],
        ];
    }

    /**
     * @param array $aCards
     *
     * @return array
     *
     * @since 1.3.0
     */
    private function _mapCardsToList($aCards)
    {
        $aTableMapping = [];
        foreach ($aCards as $aCard) {
            $aTableMapping[] = [
                ['text' => self::_createRadioButton($aCard['TOKEN'])],
                ['text' =>
                    self::_createDescription(
                        $aCard['MASKEDPAN'],
                        $aCard['EXPIRATIONMONTH'],
                        $aCard['EXPIRATIONYEAR']
                    ),
                ],
                ['text' => self::_createDeleteButton($aCard['OXID'])],
            ];
        }
        if ($aCards) {
            $aTableMapping[] = [
                ['text' => self::_createRadioButton(self::NEW_CARD_TOKEN, true)],
                ['text' => Helper::translate('wd_vault_use_new_text')],
            ];
        }
        return [
            'body' => $aTableMapping,
        ];
    }

    /**
     * @param string $sToken
     * @param bool   $bChecked
     *
     * @return string
     *
     * @since 1.3.0
     */
    private static function _createRadioButton($sToken, $bChecked = false)
    {
        $sResult = '<input type="radio" name="dynvalue[wd_selected_card]" value="' . $sToken . '"';

        if ($bChecked) {
            $sResult .= ' checked';
        }

        $sResult .= ' />';

        return $sResult;
    }

    /**
     * @param int $iCardId
     *
     * @return string
     *
     * @since 1.3.0
     */
    private static function _createDeleteButton($iCardId)
    {
        return '<button class="btn btn-error" type="submit" name="wd_deletion_card_id" value="' . $iCardId . '" />' .
            Helper::translate('wd_text_delete') . ' </button > ';
    }

    /**
     * @param string $sMaskedPan
     * @param int    $iExpMonth
     * @param int    $iExpYear
     *
     * @return string
     *
     * @since 1.3.0
     */
    private static function _createDescription($sMaskedPan, $iExpMonth, $iExpYear)
    {
        return '<b>' . $sMaskedPan . '</b><i style="margin-left: 2em">' .
            sprintf("%02d", $iExpMonth) . '-' . $iExpYear . '</i>';
    }

    /**
     * @return bool
     *
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     *
     * @since 1.3.0
     */
    private static function _hasShippingAddressChanged()
    {
        $oLastAddress = OrderHelper::getLastOrderShippingAddress(Registry::getSession()->getUser()->getId());
        $oOrder = oxNew(Order::class);

        $oCurrentAddress = $oOrder->getDelAddressInfo();
        return $oCurrentAddress !== $oLastAddress;
    }

    /**
     * @inheritdoc
     *
     * @param CreditCardTransaction $oTransaction
     * @param Order                 $oOrder
     *
     * @since 1.3.0
     */
    public function addMandatoryTransactionData(&$oTransaction, $oOrder)
    {
        $aDynValue = Registry::getSession()->getVariable('dynvalue');

        if (self::isCardTokenSet($aDynValue)) {
            $oTransaction->setTokenId($aDynValue['wd_selected_card']);
        }

        parent::addMandatoryTransactionData($oTransaction, $oOrder);
    }

    /**
     * Checks if a valid card token is set
     *
     * @param array $aDynValue
     *
     * @return bool
     *
     * @since 1.3.0
     */
    public static function isCardTokenSet($aDynValue)
    {
        return isset($aDynValue['wd_selected_card']) &&
            $aDynValue['wd_selected_card'] !== CreditCardPaymentMethod::NEW_CARD_TOKEN;
    }
}
