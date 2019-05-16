<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Config\SepaConfig;
use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;
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
     * @return Config
     *
     * @since 1.0.0
     */
    public function getConfig()
    {
        $oConfig = parent::getConfig();

        $oPaymentMethodConfig = new PaymentMethodConfig(
            SofortTransaction::NAME,
            $this->_oPayment->oxpayments__wdoxidee_maid->value,
            $this->_oPayment->oxpayments__wdoxidee_secret->value
        );
        $oConfig->add($oPaymentMethodConfig);

        $oSepaCtPayment = PaymentMethodHelper::getPaymentById(SepaCreditTransferPaymentMethod::getName(true));
        $oSepaCtConfig = new SepaConfig(
            SepaCreditTransferTransaction::NAME,
            $oSepaCtPayment->oxpayments__wdoxidee_maid->value,
            $oSepaCtPayment->oxpayments__wdoxidee_secret->value
        );
        $oConfig->add($oSepaCtConfig);

        return $oConfig;
    }

    /**
     * Get the current transaction to be processed
     *
     * @return Transaction
     *
     * @since 1.0.0
     */
    public function getTransaction()
    {
        return new SofortTransaction();
    }

    /**
     * Sofort has a variable logo depending on the shop language
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getLogoPath()
    {
        $sLogoPath = $this->_oPayment->oxpayments__wdoxidee_logo->value;
        $sCountryCode = $this->_oPayment->oxpayments__wdoxidee_countrycode->value;
        $sLogoVariant = $this->_oPayment->oxpayments__wdoxidee_logovariant->value;

        return sprintf(
            $sLogoPath,
            $sCountryCode,
            $sLogoVariant
        );
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
            'countryCode' => [
                'type'        => 'text',
                'field'       => 'oxpayments__wdoxidee_countrycode',
                'title'       => Helper::translate('wd_config_country_code'),
                'description' => Helper::translate('wd_config_country_code_desc'),
                'onchange'    => 'wdCheckCountryCode()',
            ],
            'logoType' => [
                'type'        => 'select',
                'field'       => 'oxpayments__wdoxidee_logovariant',
                'options'     => [
                    'standard'      => Helper::translate('wd_text_logo_variant_standard'),
                    'descriptive'   => Helper::translate('wd_text_logo_variant_descriptive'),
                ],
                'title'       => Helper::translate('wd_config_logo_variant'),
                'description' => Helper::translate('wd_config_logo_variant_desc'),
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
            ['additionalInfo', 'countryCode', 'logoType', 'deleteCanceledOrder', 'deleteFailedOrder']
        );
    }

    /**
     * @inheritdoc
     *
     * @return SepaCreditTransferPaymentMethod
     *
     * @since 1.0.1
     */
    public function getPostProcessingPaymentMethod()
    {
        return new SepaCreditTransferPaymentMethod();
    }
}
