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
use Wirecard\PaymentSdk\Transaction\SofortTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

use Wirecard\Oxid\Core\Helper;

/**
 * Payment method implementation for Sofort.
 *
 * @since 1.0.0
 */
class SofortPaymentMethod extends PaymentMethod
{
    /**
     * @inheritdoc
     *
     * @since 1.0.0
     */
    protected static $_sName = "sofortbanking";

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
            SofortTransaction::NAME,
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
     * @since 1.0.0
     */
    public function getTransaction(): Transaction
    {
        return new SofortTransaction();
    }

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.0.0
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
            ],
            'countryCode' => [
                'type'        => 'text',
                'field'       => 'oxpayments__wdoxidee_countrycode',
                'title'       => Helper::translate('config_country_code'),
                'description' => Helper::translate('config_country_code_desc'),
                'onchange'    => 'wdCheckCountryCode()',
            ],
            'logoType' => [
                'type'        => 'select',
                'field'       => 'oxpayments__wdoxidee_logovariant',
                'options'     => [
                    'standard'      => Helper::translate('text_logo_variant_standard'),
                    'descriptive'   => Helper::translate('text_logo_variant_descriptive'),
                ],
                'title'       => Helper::translate('config_logo_variant'),
                'description' => Helper::translate('config_logo_variant_desc'),
            ],
        ];

        return array_merge(parent::getConfigFields(), $aAdditionalFields);
    }
}
