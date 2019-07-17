<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model\PaymentMethod;

use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Core\Helper;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\BankAccount;
use Wirecard\PaymentSdk\Transaction\GiropayTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

/**
 * Payment method implementation for giropay.
 *
 * @since 1.2.0
 */
class GiropayPaymentMethod extends SepaCreditTransferPaymentMethod
{
    /**
     * @inheritdoc
     *
     * @since 1.2.0
     */
    protected static $_sName = 'giropay';

    /**
     * @inheritdoc
     *
     * @var bool
     *
     * @since 1.2.0
     */
    protected static $_bMerchantOnly = false;

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
            GiropayTransaction::NAME,
            $this->_oPayment->oxpayments__wdoxidee_maid->value,
            $this->_oPayment->oxpayments__wdoxidee_secret->value
        );

        $oConfig->add($oPaymentMethodConfig);
        return $oConfig;
    }

    /**
     * Get the current transaction to be processed
     *
     * @return Transaction
     *
     * @since 1.2.0
     */
    public function getTransaction()
    {
        return new GiropayTransaction();
    }

    /**
     * @inheritdoc
     *
     * @param GiropayTransaction $oTransaction
     * @param Order              $oOrder
     *
     * @since 1.2.0
     */
    public function addMandatoryTransactionData(&$oTransaction, $oOrder)
    {
        $oBankAccount = new BankAccount();
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');

        $oBankAccount->setBic($aDynvalues['bic'] ?? '');
        $oTransaction->setBankAccount($oBankAccount);
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
                'type'  => 'select',
                'field' => 'oxpayments__wdoxidee_descriptor',
                'options' => [
                    '1' => Helper::translate('wd_yes'),
                    '0' => Helper::translate('wd_no'),
                ],
                'title' => Helper::translate('wd_config_descriptor'),
                'description' => Helper::translate('wd_config_descriptor_desc'),
            ],
            'additionalInfo' => [
                'type'  => 'select',
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
        ];

        return array_merge(parent::getConfigFields(), $aAdditionalFields);
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
        return [
            'bic' => [
                'type' => 'text',
                'title' => Helper::translate('wd_bic'),
                'required' => true,
            ],
        ];
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
        return array_merge(parent::getPublicFieldNames(), [
            'descriptor',
            'additionalInfo',
            'deleteCanceledOrder',
            'deleteFailedOrder',
        ]);
    }
}
