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
use Wirecard\Oxid\Extend\Model\Payment;
use Wirecard\Oxid\Model\Transaction as TransactionModel;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\Transaction;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;

/**
 * Payment method implementation for Paypal
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
     * @param Payment $oPayment
     *
     * @return Config
     *
     * @since 1.0.0
     */
    public function getConfig($oPayment): Config
    {
        $oConfig = parent::getConfig($oPayment);

        $oPaymentMethodConfig = new PaymentMethodConfig(
            PayPalTransaction::NAME,
            $oPayment->oxpayments__wdoxidee_maid->value,
            $oPayment->oxpayments__wdoxidee_secret->value
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
                    '1' => Helper::translate('yes'),
                    '0' => Helper::translate('no'),
                ],
                'title' => Helper::translate('config_shopping_basket'),
                'description' => Helper::translate('config_shopping_basket_desc'),
            ],
            'descriptor' => [
                'type'        => 'select',
                'field'       => 'oxpayments__wdoxidee_descriptor',
                'options'     => [
                    '1'       => Helper::translate('yes'),
                    '0'       => Helper::translate('no'),
                ],
                'title'       => Helper::translate('config_descriptor'),
                'description' => Helper::translate('config_descriptor_desc'),
            ],
            'additionalInfo' => [
                'type'        => 'select',
                'field'       => 'oxpayments__wdoxidee_additional_info',
                'options'     => [
                    '1'       => Helper::translate('yes'),
                    '0'       => Helper::translate('no'),
                ],
                'title'       => Helper::translate('config_additional_info'),
                'description' => Helper::translate('config_additional_info_desc'),
            ],
            'paymentAction' => [
                'type'        => 'select',
                'field'       => 'oxpayments__wdoxidee_transactionaction',
                'options'     => TransactionModel::getTranslatedActions(),
                'title'       => Helper::translate('config_payment_action'),
                'description' => Helper::translate('config_payment_action_desc'),
            ],
        ];

        return array_merge(parent::getConfigFields(), $aAdditionalFields);
    }
}
