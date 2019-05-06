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
use OxidEsales\Eshop\Application\Model\Payment;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\BankAccount;
use Wirecard\PaymentSdk\Transaction\GiropayTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

use Wirecard\Oxid\Core\Helper;

/**
 * Payment method implementation for Giropay.
 *
 * @since 1.1.0
 */
class GiropayPaymentMethod extends PaymentMethod
{
    /**
     * @inheritdoc
     *
     * @since 1.1.0
     */
    protected static $_sName = 'giropay';

    /**
     * @inheritdoc
     *
     * @param Payment $oPayment
     *
     * @return Config
     *
     * @since 1.1.0
     */
    public function getConfig($oPayment): Config
    {
        $oConfig = parent::getConfig($oPayment);

        $oPaymentMethodConfig = new PaymentMethodConfig(
            GiropayTransaction::NAME,
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
     * @since 1.1.0
     */
    public function getTransaction(): Transaction
    {
        $oTransaction = new GiropayTransaction();
        $oBankAccount = new BankAccount();
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');

        $oBankAccount->setBic($aDynvalues['bic']);
        $oTransaction->setBankAccount($oBankAccount);

        return $oTransaction;
    }

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.1.0
     */
    public function getConfigFields(): array
    {
        $aAdditionalFields = [
            'additionalInfo' => [
                'type'  => 'select',
                'field' => 'oxpayments__wdoxidee_additional_info',
                'options' => [
                    '1' => Helper::translate('yes'),
                    '0' => Helper::translate('no'),
                ],
                'title' => Helper::translate('config_additional_info'),
                'description' => Helper::translate('config_additional_info_desc'),
            ],
            'deleteCanceledOrder' => [
                'type' => 'select',
                'field' => 'oxpayments__wdoxidee_delete_canceled_order',
                'options' => [
                    '1' => Helper::translate('yes'),
                    '0' => Helper::translate('no'),
                ],
                'title' => Helper::translate('config_delete_cancel_order'),
                'description' => Helper::translate('config_delete_cancel_order_desc'),
            ],
            'deleteFailedOrder' => [
                'type' => 'select',
                'field' => 'oxpayments__wdoxidee_delete_failed_order',
                'options' => [
                    '1' => Helper::translate('yes'),
                    '0' => Helper::translate('no'),
                ],
                'title' => Helper::translate('config_delete_failure_order'),
                'description' => Helper::translate('config_delete_failure_order_desc'),
            ],
        ];

        return array_merge(parent::getConfigFields(), $aAdditionalFields);
    }

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.1.0
     */
    public function getCheckoutFields()
    {
        return [
            'bic' => [
                'type' => 'text',
                'title' => Helper::translate('bic_input'),
                'required' => true,
            ],
        ];
    }

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.1.0
     */
    public function getPublicFieldNames()
    {
        return array_merge(parent::getPublicFieldNames(), [
            'additionalInfo',
            'deleteCanceledOrder',
            'deleteFailedOrder',
        ]);
    }
}
