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

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\EpsTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

use Wirecard\Oxid\Core\Helper;

/**
 * Payment method implementation for eps
 *
 * @since 1.0.1
 */
class EpsPaymentMethod extends PaymentMethod
{
    /**
     * @inheritdoc
     *
     * @since 1.0.1
     */
    protected static $_sName = "eps";

    /**
     * @inheritdoc
     *
     * @param Payment $oPayment
     *
     * @return Config
     *
     * @since 1.0.1
     */
    public function getConfig($oPayment): Config
    {
        $oConfig = parent::getConfig($oPayment);

        $oPaymentMethodConfig = new PaymentMethodConfig(
            EpsTransaction::NAME,
            $oPayment->oxpayments__wdoxidee_maid->value,
            $oPayment->oxpayments__wdoxidee_secret->value
        );

        $oConfig->add($oPaymentMethodConfig);

        return $oConfig;
    }

    /**
     * Get the current transaction to be processed
     *
     * @return Transaction
     *
     * @since 1.0.1
     */
    public function getTransaction(): Transaction
    {
        return new EpsTransaction();
    }

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.0.1
     */
    public function getConfigFields(): array
    {
        $aAdditionalFields = [
            'additionalInfo' => [
                'type'        => 'select',
                'field'       => 'oxpayments__wdoxidee_additional_info',
                'options'     => [
                    '1'       => Helper::translate('yes'),
                    '0'       => Helper::translate('no'),
                ],
                'title'       => Helper::translate('config_additional_info'),
                'description' => Helper::translate('config_additional_info_desc'),
            ]
        ];

        return array_merge(parent::getConfigFields(), $aAdditionalFields);
    }
}
