<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use Exception;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Registry;

use Psr\Log\LoggerInterface;

use Wirecard\Oxid\Core\Helper;
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
     * @var LoggerInterface
     *
     * @since 1.0.0
     */
    protected $_oLogger;

    /**
     * PaymentMethod constructor.
     *
     * @throws Exception if payment method name is not overwritten in child class
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->_oLogger = Registry::getLogger();

        if ($this::$_sName == 'undefined') {
            throw new Exception("payment method name not defined: " . get_class());
        }
    }

    /**
     * Get the payments method configuration
     *
     * @param Payment $oPayment
     *
     * @return Config
     *
     * @since 1.0.0
     */
    public function getConfig($oPayment)
    {
        $oConfig = new Config(
            $oPayment->oxpayments__wdoxidee_apiurl->value,
            $oPayment->oxpayments__wdoxidee_httpuser->value,
            $oPayment->oxpayments__wdoxidee_httppass->value
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
     * Get the payment methods name
     *
     * @param bool $bForOxid
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @since 1.0.0
     */
    public static function getName(bool $bForOxid = false): string
    {
        $childClass = get_called_class();

        if ($bForOxid) {
            return self::OXID_NAME_PREFIX . $childClass::$_sName;
        }

        return $childClass::$_sName;
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
    public static function getOxidFromSDKName(string $sSDKName): string
    {
        return self::OXID_NAME_PREFIX . $sSDKName;
    }

    /**
     * Returns the logo path for a payment method.
     *
     * @param Payment $oPayment
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getLogoPath($oPayment)
    {
        $sLogoFile = $oPayment->oxpayments__wdoxidee_logo->value;

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
                'title' => Helper::translate('wdpg_config_base_url'),
                'description' => Helper::translate('wdpg_config_base_url_desc'),
            ],
            'httpUser' => [
                'type' => 'text',
                'field' => 'oxpayments__wdoxidee_httpuser',
                'title' => Helper::translate('wdpg_config_http_user'),
            ],
            'httpPassword' => [
                'type' => 'text',
                'field' => 'oxpayments__wdoxidee_httppass',
                'title' => Helper::translate('wdpg_config_http_password'),
            ],
            'testCredentials' => [
                'type' => 'button',
                'onclick' => 'wdTestPaymentMethodCredentials()',
                'text' => Helper::translate('wdpg_test_credentials'),
                'colspan' => '2',
            ],
            'maid' => [
                'type' => 'text',
                'field' => 'oxpayments__wdoxidee_maid',
                'title' => Helper::translate('wdpg_config_merchant_account_id'),
                'description' => Helper::translate('wdpg_config_merchant_account_id_desc'),
            ],
            'secret' => [
                'type' => 'text',
                'field' => 'oxpayments__wdoxidee_secret',
                'title' => Helper::translate('wdpg_config_merchant_secret'),
                'description' => Helper::translate('wdpg_config_merchant_secret_desc'),
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
     * @SuppressWarnings(PHPMD)
     *
     * @since 1.0.0
     */
    public function getSupportConfigFields()
    {
        $aFieldsPublic = array_filter($this->getConfigFields(), function ($field, $key) {
            return in_array($key, $this->getPublicFieldNames());
        }, ARRAY_FILTER_USE_BOTH);

        return $aFieldsPublic;
    }
}
