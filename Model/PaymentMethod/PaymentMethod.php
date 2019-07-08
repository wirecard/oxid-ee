<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model\PaymentMethod;

use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;

use Psr\Log\LoggerInterface;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\Oxid\Extend\Model\Order;
use Wirecard\Oxid\Extend\Model\Payment;

use Wirecard\PaymentSdk\Config\Config;

/**
 * Class PaymentMethod
 *
 * @since 1.0.0
 */
abstract class PaymentMethod
{
    const OXID_NAME_PREFIX = 'wd';

    /**
     * @var string
     *
     * @since 1.0.0
     */
    protected static $_sName = 'undefined';

    /**
     * @var bool
     *
     * @since 1.1.0
     */
    protected static $_bMerchantOnly = false;

    /**
     * @var LoggerInterface
     *
     * @since 1.0.0
     */
    protected $_oLogger;

    /**
     * @var Payment
     *
     * @since 1.1.0
     */
    protected $_oPayment;

    /**
     * PaymentMethod constructor.
     *
     * @throws StandardException if payment method name is not overwritten in child class
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->_oLogger = Registry::getLogger();

        if ($this::$_sName == 'undefined') {
            throw new StandardException("payment method name not defined: " . get_class());
        }

        $this->_oPayment = PaymentMethodHelper::getPaymentById(self::getName());
    }

    /**
     * Get the payments method configuration
     *
     * @return Config
     *
     * @since 1.0.0
     */
    public function getConfig()
    {
        $oConfig = new Config(
            $this->_oPayment->oxpayments__wdoxidee_apiurl->value,
            $this->_oPayment->oxpayments__wdoxidee_httpuser->value,
            $this->_oPayment->oxpayments__wdoxidee_httppass->value
        );

        self::_addAnalyticsShopInfo($oConfig);

        return $oConfig;
    }

    /**
     * Adds additional information (shop info and plugin info) to the config object
     *
     * @param Config $oConfig
     *
     * @since 1.2.0
     */
    protected static function _addAnalyticsShopInfo(&$oConfig)
    {
        $aShopInfoFields = Helper::getShopInfoFields();
        $oConfig->setShopInfo($aShopInfoFields[Helper::SHOP_SYSTEM_KEY], $aShopInfoFields[Helper::SHOP_VERSION_KEY]);
        $oConfig->setPluginInfo(
            $aShopInfoFields[Helper::PLUGIN_NAME_KEY],
            $aShopInfoFields[Helper::PLUGIN_VERSION_KEY]
        );
    }

    /**
     * Get the payments method transaction configuration
     *
     * @since 1.0.0
     */
    abstract public function getTransaction();

    /**
     * Get the payments for the payment method
     *
     * @return Payment
     *
     * @since 1.1.0
     */
    public function getPayment()
    {
        return $this->_oPayment;
    }

    /**
     * Get the payment methods name used in OXID
     *
     * @return string
     *
     * @since 1.3.0
     */
    public static function getName()
    {
        $oChildClass = get_called_class();
        return self::OXID_NAME_PREFIX . $oChildClass::$_sName;
    }

    /**
     * Returns the prefixed Oxid payment method name
     *
     * @param string $sSDKName
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function getOxidFromSDKName($sSDKName)
    {
        return self::OXID_NAME_PREFIX . $sSDKName;
    }

    /**
     * Returns the logo path for a payment method.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getLogoPath()
    {
        $sLogoFile = $this->_oPayment->oxpayments__wdoxidee_logo->value;

        $oConfig = Registry::getConfig();

        return $oConfig->getShopUrl() . $oConfig->getModulesDir(false) .
            "wirecard/paymentgateway/out/img/{$sLogoFile}";
    }

    /**
     * Returns an array of fields to be displayed in the payment method config.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getConfigFields()
    {
        return [
            'apiUrl' => [
                'type' => 'text',
                'field' => 'oxpayments__wdoxidee_apiurl',
                'title' => Helper::translate('wd_config_base_url'),
                'description' => Helper::translate('wd_config_base_url_desc'),
            ],
            'httpUser' => [
                'type' => 'text',
                'field' => 'oxpayments__wdoxidee_httpuser',
                'title' => Helper::translate('wd_config_http_user'),
            ],
            'httpPassword' => [
                'type' => 'text',
                'field' => 'oxpayments__wdoxidee_httppass',
                'title' => Helper::translate('wd_config_http_password'),
            ],
            'testCredentials' => [
                'type' => 'button',
                'onclick' => 'wdTestPaymentMethodCredentials()',
                'text' => Helper::translate('wd_test_credentials'),
                'colspan' => '2',
            ],
            'maid' => [
                'type' => 'text',
                'field' => 'oxpayments__wdoxidee_maid',
                'title' => Helper::translate('wd_config_merchant_account_id'),
                'description' => Helper::translate('wd_config_merchant_account_id_desc'),
            ],
            'secret' => [
                'type' => 'text',
                'field' => 'oxpayments__wdoxidee_secret',
                'title' => Helper::translate('wd_config_merchant_secret'),
                'description' => Helper::translate('wd_config_merchant_secret_desc'),
            ],
        ];
    }

    /**
     * Returns an array of fields to be displayed in the checkout flow.
     *
     * @return array
     *
     * @since 1.1.0
     */
    public function getCheckoutFields()
    {
        return [];
    }

    /**
     * Returns array of setting names which should be part of output debug infos
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getPublicFieldNames()
    {
        return ['apiUrl', 'maid'];
    }

    /**
     * Returns an array of all meta data fields for a payment method.
     *
     * @return array
     *
     * @since 1.2.0
     */
    public function getMetaDataFieldNames()
    {
        return ['initial_title'];
    }

    /**
     * Returns an array of fields to be displayed in support email
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getSupportConfigFields()
    {
        $aFieldsPublic = array_filter($this->getConfigFields(), function ($aField, $sKey) {
            return in_array($sKey, $this->getPublicFieldNames());
        }, ARRAY_FILTER_USE_BOTH);

        return $aFieldsPublic;
    }

    /**
     * Adds all mandatory transaction data
     *
     * @param \Wirecard\PaymentSdk\Transaction\Transaction $oTransaction
     * @param Order                                        $oOrder
     *
     * @since 1.1.0
     */
    public function addMandatoryTransactionData(&$oTransaction, $oOrder)
    {
    }

    /**
     * Returns the post-processing transaction for this payment method
     *
     * @param string                           $sAction
     * @param \Wirecard\Oxid\Model\Transaction $oParentTransaction
     * @param array|null                       $aOrderItems
     *
     * @return \Wirecard\PaymentSdk\Transaction\Transaction
     *
     * @since 1.1.0
     */
    public function getPostProcessingTransaction($sAction, $oParentTransaction, $aOrderItems = null)
    {
        return $this->getTransaction();
    }

    /**
     * Returns true if the payment method can only be used by the merchant and should be hidden for the user
     *
     * @return bool
     *
     * @since 1.1.0
     */
    public function isMerchantOnly()
    {
        $oChildClass = get_called_class();
        return $oChildClass::$_bMerchantOnly;
    }

    /**
     * Function to be run before a transaction is generated for the payment method.
     *
     * @since 1.1.0
     */
    public function onBeforeTransactionCreation()
    {
    }

    /**
     * Function to be run before an order is generated for the payment method.
     *
     * @since 1.2.0
     */
    public function onBeforeOrderCreation()
    {
    }

    /**
     * Returns true if payment method should be visible for the user
     *
     * @return bool
     *
     * @since 1.2.0
     */
    public function isPaymentPossible()
    {
        return true;
    }

    /**
     * Get the keys that should not be included for this payment method
     *
     * @return array
     *
     * @since 1.3.0
     */
    public function getHiddenAccountHolderFields()
    {
        return [];
    }
}
