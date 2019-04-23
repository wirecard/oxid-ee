<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Application\Model\Shop;
use OxidEsales\Eshop\Core\Model\ListModel;
use OxidEsales\Eshop\Core\Module\Module;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\ShopVersion;

use Exception;
use DateTime;

/**
 * Util functions
 *
 * @since 1.0.0
 */
class Helper
{

    const MODULE_ID = 'wdoxidee';
    const SHOP_SYSTEM_KEY = 'shopSystem';
    const SHOP_NAME_KEY = 'shopName';
    const SHOP_VERSION_KEY = 'shopVersion';
    const PLUGIN_NAME_KEY = 'pluginName';
    const PLUGIN_VERSION_KEY = 'pluginVersion';
    const SHOP_SYSTEM_VALUE = 'OXID';

    /**
     * Gets the translation for a given key.
     *
     * @param string $sKey
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function translate($sKey)
    {
        return Registry::getLang()->translateString($sKey);
    }

    /**
     * Create a Fingerprint for the Device.fingerprint fraud protection
     *
     * also used in out/blocks/profiling_tags.tpl for the session id
     *
     * @param string $sMaid
     * @param string $sSessionId
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function createDeviceFingerprint($sMaid, $sSessionId = null)
    {
        return $sMaid . '_' . $sSessionId;
    }

    /**
     * Returns a list of available payments.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public static function getPayments()
    {
        $oPaymentList = oxNew(ListModel::class);
        $oPaymentList->init(Payment::class);

        return $oPaymentList->getList()->getArray();
    }

    /**
     * Returns a list of available payments added by the module.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public static function getModulePayments()
    {
        return array_filter(self::getPayments(), function ($oPayment) {
            return $oPayment->isCustomPaymentMethod();
        });
    }

    /**
     * Returns a list of available payments including inactive ones.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public static function getPaymentsIncludingInactive()
    {
        $oPaymentList = oxNew(ListModel::class);
        $oPaymentList->init(Payment::class);

        return $oPaymentList->getListIncludingInactive()->getArray();
    }

    /**
     * Returns a list of all payments added by the module including inactive ones.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public static function getModulePaymentsIncludingInactive()
    {
        return array_filter(self::getPaymentsIncludingInactive(), function ($oPayment) {
            return $oPayment->oxpayments__wdoxidee_isours->value;
        });
    }

    /**
     * Converts a string to float while acknowledging different number formats with diferent decimal points and
     * thousand separators.
     *
     * @param string $sNumber
     *
     * @return float
     *
     * @since 1.0.0
     */
    public static function getFloatFromString($sNumber)
    {
        return (float) preg_replace('/\.(?=.*\.)/', '', str_replace(',', '.', $sNumber));
    }

    /**
     * Returns the gender code for a given salutation.
     *
     * @param string $sSalutation
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function getGenderCodeForSalutation($sSalutation)
    {
        $aGenderCodeMap = [
            'MR' => 'm',
            'MRS' => 'f',
        ];

        return $aGenderCodeMap[$sSalutation] ?? '';
    }

    /**
     * Converts a time string to a DateTime object. If the string is not valid (e.g. OXID's default date value
     * 0000-00-00), null will be returned.
     *
     * @param string $sTime
     *
     * @return DateTime|null
     *
     * @since 1.0.0
     */
    public static function getDateTimeFromString($sTime)
    {
        try {
            $oDateTime = new DateTime($sTime);
            $aErrorInformation = DateTime::getLastErrors();

            if ($aErrorInformation['warning_count'] !== 0) {
                return null;
            }

            return $oDateTime;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Returns HTML for the [{oxinputhelp}] Smarty function, but allows passing any string, not just translation keys.
     *
     * @param string $sText
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function getInputHelpHtml($sText)
    {
        $oSmarty = Registry::getUtilsView()->getSmarty();

        $oSmarty->assign('sHelpId', md5($sText));
        $oSmarty->assign('sHelpText', $sText);

        return $oSmarty->fetch('inputhelp.tpl');
    }

    /**
     * Returns the session challenge variable from the session object
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function getSessionChallenge()
    {
        return Registry::getSession()->getVariable('sess_challenge');
    }

    /**
     * Checks if a key is present and not empty in the array passed as an argument
     *
     * @param array  $aArgs
     * @param string $sKey
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public static function isPresentProperty($aArgs, $sKey)
    {
        return isset($aArgs[$sKey]) && !empty($aArgs[$sKey]);
    }

    /**
     * Gets the session id as a query string
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function getSidQueryString()
    {
        $sSid = Registry::getSession()->sid(true);
        if ($sSid != '') {
            $sSid = '&' . $sSid;
        }

        return $sSid;
    }

    /**
     * @param string $sTimeStamp
     *
     * @return bool|string
     *
     * @since 1.0.0
     */
    public static function getFormattedDbDate($sTimeStamp = null)
    {
        $oUtilsDate = Registry::getUtilsDate();
        return $oUtilsDate->formatDBTimestamp($oUtilsDate->formTime($sTimeStamp));
    }

    /**
     * Validates the string for e-mail address format
     *
     * @param string $sEmail
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public static function isEmailValid($sEmail)
    {
        return !!filter_var($sEmail, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Returns list of modules
     *
     * @return array;
     *
     * @since 1.0.0
     */
    public static function getModulesList()
    {
        $sModulesDir = Registry::getConfig()->getModulesDir();
        $oModuleList = oxNew(\OxidEsales\Eshop\Core\Module\ModuleList::class);
        $aModules = $oModuleList->getModulesFromDir($sModulesDir);
        return $aModules;
    }

    /**
     * Check if $sModuleId is this plugin id
     *
     * @param string $sModuleId
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public static function isThisModule($sModuleId)
    {
        return $sModuleId === self::MODULE_ID;
    }

    /**
     * @return array
     *
     * @since 1.0.0
     */
    public static function getShopInfoFields()
    {
        $sShopId = Registry::getConfig()->getShopId();
        $oShop = oxNew(Shop::class);
        $oShop->load($sShopId);

        $oModule = oxNew(Module::class);
        $oModule->load(Helper::MODULE_ID);

        return [
            self::SHOP_SYSTEM_KEY => self::SHOP_SYSTEM_VALUE,
            self::SHOP_NAME_KEY => $oShop->oxshops__oxname->value,
            self::SHOP_VERSION_KEY => ShopVersion::getVersion(),
            self::PLUGIN_NAME_KEY => $oModule->getTitle(),
            self::PLUGIN_VERSION_KEY => $oModule->getInfo('version'),
        ];
    }
}
