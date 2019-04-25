<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Model\Transaction as TransactionModel;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\Transaction;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;

/**
 * Payment method implementation for PayPal
 *
 * @since 1.0.0
 */
class PaypalPaymentMethod extends PaymentMethod
{
    /**
     * @inheritdoc
     *
     * @since 1.0.0
     */
    protected static $_sName = "paypal";

    /**
     * @inheritdoc
     *
     * @return Config
     *
     * @since 1.0.0
     */
    public function getConfig()
    {
        $oConfig = parent::getConfig();

        $oPaymentMethodConfig = new PaymentMethodConfig(
            PayPalTransaction::NAME,
            $this->oPayment->oxpayments__wdoxidee_maid->value,
            $this->oPayment->oxpayments__wdoxidee_secret->value
        );

        $oConfig->add($oPaymentMethodConfig);
        return $oConfig;
    }

    /**
     * @inheritdoc
     *
     * @return Transaction
     *
     * @since 1.0.0
     */
    public function getTransaction()
    {
        return new PayPalTransaction();
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
        $aAdditionalFields = [
            'basket' => [
                'type' => 'select',
                'field' => 'oxpayments__wdoxidee_basket',
                'options' => [
                    '1' => Helper::translate('wd_yes'),
                    '0' => Helper::translate('wd_no'),
                ],
                'title' => Helper::translate('wd_config_shopping_basket'),
                'description' => Helper::translate('wd_config_shopping_basket_desc'),
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

        return parent::getConfigFields() + $aAdditionalFields;
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
            ['basket', 'descriptor', 'additionalInfo', 'paymentAction', 'deleteCanceledOrder', 'deleteFailedOrder']
        );
    }
}
