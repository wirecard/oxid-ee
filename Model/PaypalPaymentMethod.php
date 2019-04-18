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

use \Wirecard\PaymentSdk\Config\Config;
use \Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use \Wirecard\PaymentSdk\Transaction\Transaction;
use \Wirecard\PaymentSdk\Transaction\PayPalTransaction;

use \OxidEsales\Eshop\Application\Model\Payment;

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
        $parentConfigFields = parent::getConfigFields();
        $additionalFields = [
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
        ];

        return array_merge($parentConfigFields, $additionalFields);
    }
}
