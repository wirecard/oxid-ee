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
use OxidEsales\Eshop\Core\Exception\StandardException;

use Psr\Log\LoggerInterface;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\PaymentMethodHelper;
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
     * @since 1.0.1
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
     * @since 1.0.1
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

        $this->_oPayment = PaymentMethodHelper::getPaymentById(self::getName(true));
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

        $aShopInfoFields = Helper::getShopInfoFields();
        $oConfig->setShopInfo($aShopInfoFields[Helper::SHOP_SYSTEM_KEY], $aShopInfoFields[Helper::SHOP_VERSION_KEY]);
        $oConfig->setPluginInfo(
            $aShopInfoFields[Helper::PLUGIN_NAME_KEY],
            $aShopInfoFields[Helper::PLUGIN_VERSION_KEY]
        );

        return $oConfig;
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
     * @since 1.0.1
     */
    public function getPayment()
    {
        return $this->_oPayment;
    }

    /**
     * Get the payment methods name
     *
     * @param bool $bForOxid
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function getName($bForOxid = false)
    {
        $oChildClass = get_called_class();

        if ($bForOxid) {
            return self::OXID_NAME_PREFIX . $oChildClass::$_sName;
        }

        return $oChildClass::$_sName;
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
     * Adds all needed data to the post-processing transaction
     *
     * @param Transaction $oTransaction
     * @param Transaction $oParentTransaction
     *
     * @since 1.0.1
     */
    public function addPostProcessingTransactionData(&$oTransaction, $oParentTransaction)
    {
    }

    /**
     * Returns the post-processing payment method for this payment method
     *
     * @return PaymentMethod
     *
     * @since 1.0.1
     */
    public function getPostProcessingPaymentMethod()
    {
        return $this;
    }

    /**
     * Returns true if the payment method can only be used by the merchant and should be hidden for the user
     *
     * @return bool
     *
     * @since 1.0.1
     */
    public function isMerchantOnly()
    {
        $oChildClass = get_called_class();
        return $oChildClass::$_bMerchantOnly;
    }
}
