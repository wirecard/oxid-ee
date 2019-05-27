<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\IdealBic;
use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

use Wirecard\Oxid\Core\Helper;

use OxidEsales\Eshop\Core\Registry;

//use ReflectionClass;

/**
 * Payment method implementation for iDEAL
 *
 * @since 1.2.0
 */
class IdealPaymentMethod extends PaymentMethod
{
    /**
     * @inheritdoc
     *
     * @since 1.2.0
     */
    protected static $_sName = "ideal";

    /**
     *
     * @since 1.2.0
     */
    private static $_aBankOptions = [];

    /**
     * IdealPaymentMethod constructor.
     *
     * @since 1.2.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->_aBankOptions = $this->getBanks();
    }

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
            IdealTransaction::NAME,
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
        $oTransaction = new IdealTransaction();

        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');

        $oTransaction->setBic($this->_aBankOptions[$aDynvalues['bank']]);
        return $oTransaction;
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
            'bank' => [
                'type' => 'select',
                'options' => $this->_aBankOptions,
                'title' => Helper::translate('wd_ideal_legend'),
            ],
        ];
    }

    /**
     * Returns array for bank select options
     *
     * @return array
     *
     * @since 1.2.0
     */
    public function getBanks()
    {
        //IdealBic extends Enum class and contains const variables which represent bank options.
        //toArray() combines all const variables from IdealBic and converts them to array.
        return IdealBic::toArray();
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

    /**
     * @inheritdoc
     *
     * @param string $sAction
     *
     * @return SepaCreditTransferPaymentMethod
     *
     * @since 1.2.0
     */
    public function getPostProcessingPaymentMethod($sAction = '')
    {
        return new SepaCreditTransferPaymentMethod();
    }
}
