<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model\PaymentMethod;

use OxidEsales\Eshop\Core\Exception\InputException;
use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\Oxid\Core\SessionHelper;
use Wirecard\Oxid\Model\Transaction as TransactionModel;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\SepaConfig;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Transaction\SepaDirectDebitTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

/**
 * Payment method implementation for SEPA Direct Debit
 *
 * @since 1.1.0
 */
class SepaDirectDebitPaymentMethod extends SepaCreditTransferPaymentMethod
{
    /**
     * @inheritdoc
     *
     * @since 1.1.0
     */
    protected static $_sName = "sepadd";

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
     * @since 1.1.0
     */
    public function getConfig()
    {
        $oConfig = parent::getConfig($this->_oPayment);

        $oPaymentMethodConfig = new SepaConfig(
            SepaDirectDebitTransaction::NAME,
            $this->_oPayment->oxpayments__wdoxidee_maid->value,
            $this->_oPayment->oxpayments__wdoxidee_secret->value
        );

        $oPaymentMethodConfig->setCreditorId($this->_oPayment->oxpayments__wdoxidee_creditorid->value);

        $oConfig->add($oPaymentMethodConfig);
        return $oConfig;
    }

    /**
     * @inheritdoc
     *
     * @return Transaction
     *
     * @since 1.1.0
     */
    public function getTransaction()
    {
        return new SepaDirectDebitTransaction();
    }

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.1.0
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
            'bic' => [
                'type' => 'select',
                'field' => 'oxpayments__wdoxidee_bic',
                'options' => [
                    '1' => Helper::translate('wd_yes'),
                    '0' => Helper::translate('wd_no'),
                ],
                'title' => Helper::translate('wd_config_enable_bic'),
            ],
            'paymentAction' => [
                'type' => 'select',
                'field' => 'oxpayments__wdoxidee_transactionaction',
                'options' => TransactionModel::getTranslatedActions(),
                'title' => Helper::translate('wd_config_payment_action'),
                'description' => Helper::translate('wd_config_payment_action_desc'),
            ],
            'creditorId' => [
                'type' => 'text',
                'field' => 'oxpayments__wdoxidee_creditorid',
                'title' => Helper::translate('wd_config_creditor_id'),
                'description' => Helper::translate('wd_config_creditor_id_desc'),
            ],
            'sepaMandateCustom' => [
                'type' => 'textarea',
                'field' => 'oxpayments__wdoxidee_sepamandatecustom',
                'title' => Helper::translate('wd_sepa_mandate'),
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
    public function getPublicFieldNames()
    {
        return array_merge(
            parent::getPublicFieldNames(),
            ['descriptor', 'additionalInfo', 'paymentAction', 'deleteCanceledOrder', 'deleteFailedOrder']
        );
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
        $aCheckoutFields = [
            'accountHolder' => [
                'type' => 'text',
                'title' => Helper::translate('wd_account_holder_title'),
                'required' => true,
            ],
            'iban' => [
                'type' => 'text',
                'title' => Helper::translate('wd_iban'),
                'required' => true,
            ],
        ];

        if ($this->_oPayment->oxpayments__wdoxidee_bic->value) {
            $aCheckoutFields = array_merge($aCheckoutFields, [
                'bic' => [
                    'type' => 'text',
                    'title' => Helper::translate('wd_bic'),
                    'required' => true,
                ],
            ]);
        }

        return $aCheckoutFields;
    }

    /**
     * @inheritdoc
     *
     * @param SepaDirectDebitTransaction $oTransaction
     * @param Order                      $oOrder
     *
     * @since 1.1.0
     */
    public function addMandatoryTransactionData(&$oTransaction, $oOrder)
    {
        $oTransaction->setIban(SessionHelper::getIban());
        $sBic = SessionHelper::getBic();

        if ($sBic) {
            $oTransaction->setBic($sBic);
        }

        $sSessionChallenge = Helper::getSessionChallenge();
        $oMandate = PaymentMethodHelper::getMandate($sSessionChallenge);
        $oTransaction->setMandate($oMandate);

        $oAccountHolder = new AccountHolder();
        $oAccountHolder->setLastName(SessionHelper::getAccountHolder());
        $oTransaction->setAccountHolder($oAccountHolder);
    }

    /**
     * @inheritdoc
     *
     * @param string                           $sAction
     * @param \Wirecard\Oxid\Model\Transaction $oParentTransaction
     * @param array|null                       $aOrderItems
     *
     * @return Transaction
     *
     * @since 1.1.0
     */
    public function getPostProcessingTransaction($sAction, $oParentTransaction, $aOrderItems = null)
    {
        if ($this->_isRefundAction($sAction)) {
            return parent::getPostProcessingTransaction($sAction, $oParentTransaction, $aOrderItems);
        }

        return $this->getTransaction();
    }

    /**
     * Checks if the action is refund
     *
     * @param string $sAction
     *
     * @return boolean
     *
     * @since 1.1.0
     */
    private function _isRefundAction($sAction)
    {
        return $sAction === TransactionModel::ACTION_CREDIT;
    }

    /**
     * @inheritdoc
     * @throws InputException
     *
     * @since 1.1.0
     */
    public function onBeforeTransactionCreation()
    {
        parent::onBeforeTransactionCreation();

        $oRequest = Registry::getRequest();

        if (!$oRequest->getRequestParameter('wdsepadd_checkbox')) {
            throw new InputException('Mandate information was not accepted.');
        }
    }
}
