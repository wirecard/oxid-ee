<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Model\ListModel;
use OxidEsales\Eshop\Application\Model\Payment;

use Exception;
use DateTime;

/**
 * Util functions
 *
 * @since 1.0.0
 */
class Helper
{
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
        return (float)preg_replace('/\.(?=.*\.)/', '', str_replace(',', '.', $sNumber));
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
    public static function isPresentProperty(array $aArgs, string $sKey): bool
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
    public static function getFormattedDbDate($sTimeStamp)
    {
        $oUtilsDate = Registry::getUtilsDate();
        return $oUtilsDate->formatDBTimestamp($oUtilsDate->formTime($sTimeStamp));
    }
}
