<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use \Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\CreditCardConfig;
use \Wirecard\PaymentSdk\Transaction\Transaction;
use \Wirecard\PaymentSdk\Transaction\CreditCardTransaction;

use \OxidEsales\Eshop\Application\Model\Payment;
use \OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Core\Helper;

/**
 * Payment method implementation for Credit Card
 */
class Credit_Card_Payment_Method extends Payment_Method
{
    /**
     * @inheritdoc
     */
    protected static $_sName = "creditcard";

    /**
     * Get the payment method's configuration
     *
     * @return Config
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function getConfig()
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->load(self::getName(true));

        $oConfig = new Config(
            $oPayment->oxpayments__wdoxidee_apiurl->value,
            $oPayment->oxpayments__wdoxidee_httpuser->value,
            $oPayment->oxpayments__wdoxidee_httppass->value
        );

        $oCreditCardConfig = new CreditCardConfig(
            $oPayment->oxpayments__wdoxidee_maid->value,
            $oPayment->oxpayments__wdoxidee_secret->value,
            self::getName()
        );

        if (isset($oPayment->oxpayments__wdoxidee_three_d_maid)) {
            $oCreditCardConfig->setThreeDCredentials(
                $oPayment->oxpayments__wdoxidee_three_d_maid,
                $oPayment->oxpayments__wdoxidee_three_d_secret
            );
        }

        $oConfig->add($oCreditCardConfig);

        return $oConfig;
    }

    /**
     * Get the current transaction to be processed
     *
     * @return Transaction
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function getTransaction()
    {
        return new CreditCardTransaction();
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getConfigFields()
    {
        $parentConfigFields = parent::getConfigFields();
        $additionalFields = [
            'threeDMaid' => [
                'type'        => 'text',
                'field'       => 'oxpayments__wdoxidee_three_d_maid',
                'title'       => Helper::translate('config_three_d_merchant_account_id'),
                'description' => Helper::translate('config_three_d_merchant_account_id_desc'),
            ],
            'threeDSecret' => [
                'type'        => 'text',
                'field'       => 'oxpayments__wdoxidee_three_d_secret',
                'title'       => Helper::translate('config_three_d_merchant_secret'),
                'description' => Helper::translate('config_three_d_merchant_secret_desc'),
            ],
            'nonThreeDMaxLimit' => [
                'type'        => 'text',
                'field'       => 'oxpayments__wdoxidee_non_three_d_max_limit',
                'title'       => Helper::translate('config_ssl_max_limit'),
                'description' => Helper::translate('config_ssl_max_limit_desc'),
            ],
            'threeDMinLimit' => [
                'type'        => 'text',
                'field'       => 'oxpayments__wdoxidee_three_d_min_limit',
                'title'       => Helper::translate('config_three_d_min_limit'),
                'description' => Helper::translate('config_three_d_min_limit_desc'),
            ],
            'limitsCurrency' => [
                'type'        => 'select',
                'field'       => 'oxpayments__wdoxidee_limits_currency',
                'options'     => $this->_getCurrencyOptions(),
                'title'       => Helper::translate('default_currency'),
            ],
        ];

        return array_merge($parentConfigFields, $additionalFields);
    }


    /**
     * Return array for currency select options
     *
     * @return array
     */
    private function _getCurrencyOptions()
    {
        $aCurrencies = Registry::getConfig()->getCurrencyArray();
        $aOptions = [];

        foreach ($aCurrencies as $oCurrency) {
            $aOptions[$oCurrency->name] = $oCurrency->name;
        }

        return $aOptions;
    }
}
