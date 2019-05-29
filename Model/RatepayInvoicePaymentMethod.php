<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use OxidEsales\Eshop\Core\Registry;
use DateTime;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\Oxid\Extend\Model\Order;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;

/**
 * Payment method implementation for Ratepay Invoice
 *
 * @since 1.2.0
 */
class RatepayInvoicePaymentMethod extends PaymentMethod
{

    const UNIQUE_TOKEN_VARIABLE = 'wd_ratepay_unique_token';

    /**
     * @inheritdoc
     *
     * @since 1.2.0
     */
    protected static $_sName = "ratepay-invoice";

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

        $oPaymentMethodConfig = new PaymentMethodConfig(
            RatepayInvoiceTransaction::NAME,
            $this->_oPayment->oxpayments__wdoxidee_maid->value,
            $this->_oPayment->oxpayments__wdoxidee_secret->value
        );

        $oConfig->add($oPaymentMethodConfig);
        return $oConfig;
    }

    /**
     * @inheritdoc
     *
     * @return RatepayInvoiceTransaction
     *
     * @since 1.2.0
     */
    public function getTransaction()
    {
        return new RatepayInvoiceTransaction();
    }

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.2.0
     */
    public function getConfigFields()
    {
        $aAdditionalFields = [
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
            'allowedCurrencies' => [
                'type' => 'multiselect',
                'field' => 'oxpayments__allowed_currencies',
                'options' => PaymentMethodHelper::getCurrencyOptions(),
                'title' => Helper::translate('wd_config_allowed_currencies'),
                'description' => Helper::translate('wd_config_allowed_currencies_desc'),
            ],
        ];

        return parent::getConfigFields() + $aAdditionalFields;
    }

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.2.0
     */
    public function getPublicFieldNames()
    {
        return array_merge(
            parent::getPublicFieldNames(),
            ['descriptor', 'additionalInfo', 'deleteCanceledOrder', 'deleteFailedOrder']
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
        return ['allowed_currencies'];
    }

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.2.0
     */
    public function getCheckoutFields()
    {
        $aCheckoutFields = null;

        if ($this->_checkDateOfBirthInput()) {
            $aCheckoutFields = [
                'dateOfBirth' => [
                    'type' => 'text',
                    'title' => Helper::translate('wd_birthdate_input'),
                    'description' => Helper::translate('wd_birthdate_format_user_hint'),
                    'required' => true,
                ],
            ];
        }

        if ($this->_checkPhoneInput()) {
            $aCheckoutFields = array_merge($aCheckoutFields, [
                'phone' => [
                    'type' => 'text',
                    'title' => Helper::translate('wd_phone'),
                    'required' => true,
                ],
            ]);
        }

        if ($this->_checkSaveCheckoutFields($aCheckoutFields)) {
            $aCheckoutFields = array_merge($aCheckoutFields, [
                'saveCheckoutFields' => [
                    'type' => 'select',
                    'options' => [
                        '1' => Helper::translate('wd_yes'),
                        '0' => Helper::translate('wd_no'),
                    ],
                    'title' => Helper::translate('wd_save_to_user_account'),
                ],
            ]);
        }

        return $aCheckoutFields;
    }

    /**
     * @inheritdoc
     * @param RatepayInvoiceTransaction $oTransaction
     * @param Order                     $oOrder
     *
     * @since 1.2.0
     */
    public function addMandatoryTransactionData(&$oTransaction, $oOrder)
    {
        $oSession = Registry::getSession();
        $oBasket = $oSession->getBasket();
        $oWdBasket = $oBasket->createTransactionBasket();

        $oTransaction->setBasket($oWdBasket);
        $oTransaction->setAccountHolder($oOrder->getAccountHolder());
        $oTransaction->setShipping($oOrder->getShippingAccountHolder());
        $oTransaction->setOrderNumber($oOrder->oxorder__oxid->value);

        $oTransaction->getAccountHolder()->setDateOfBirth(new DateTime(PaymentMethodHelper::getDbDateOfBirth()));
        $oTransaction->getAccountHolder()->setPhone(PaymentMethodHelper::getPhone());
    }

    /**
     * Returns true if the date of birth input field should be shown
     *
     * @return bool
     *
     * @since 1.2.0
     */
    private function _checkDateOfBirthInput()
    {
        $oUser = Registry::getSession()->getUser();
        PaymentMethodHelper::setDbDateOfBirth($oUser->oxuser__oxbirthdate->value);

        return $oUser->oxuser__oxbirthdate->value === '0000-00-00';
    }

    /**
     * Returns true if the phone number input field should be shown
     *
     * @return bool
     *
     * @since 1.2.0
     */
    private function _checkPhoneInput()
    {
        $oUser = Registry::getSession()->getUser();
        PaymentMethodHelper::setPhone($oUser->oxuser__oxfon->value);

        return $oUser->oxuser__oxfon->value === '';
    }

    /**
     * Returns true if the save checkout fields selection option should be shown
     *
     * @param array $aCheckoutFields
     *
     * @return bool
     *
     * @since 1.2.0
     */
    private function _checkSaveCheckoutFields($aCheckoutFields)
    {
        PaymentMethodHelper::setSaveCheckoutFields(0);
        return $aCheckoutFields !== null && Registry::getSession()->getUser()->oxuser__oxpassword->value !== '';
    }
}
