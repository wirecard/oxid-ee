<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use DateTime;

use OxidEsales\Eshop\Core\Registry;

/**
 * Helper class to handle session values
 *
 * @since 1.2.0
 */
class SessionHelper
{
    const DB_DATE_FORMAT = 'Y-m-d';
    const DEFAULT_DATE_OF_BIRTH = '0000-00-00';

    /**
     * Returns account holder for SEPA Direct Debit
     *
     * @return string
     *
     * @since 1.1.0
     */
    public static function getAccountHolder()
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');
        return $aDynvalues['accountHolder'];
    }

    /**
     * Returns IBAN
     *
     * @return string
     *
     * @since 1.1.0
     */
    public static function getIban()
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');
        return $aDynvalues['iban'];
    }

    /**
     * Returns BIC
     *
     * @return string
     *
     * @since 1.1.0
     */
    public static function getBic()
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');
        return $aDynvalues['bic'];
    }

    /**
     * Returns date of birth
     *
     * @return string date of birth formatted for db (format 'Y-m-d')
     *
     * @since 1.2.0
     */
    public static function getDbDateOfBirth()
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');
        $sDateOfBirth = $aDynvalues['dateOfBirth'];
        //To change the format string add a translation for another language on phrase app
        $oDateOfBirth = DateTime::createFromFormat(Helper::translate('wd_date_format_php_code'), $sDateOfBirth);

        return $oDateOfBirth
            ? $oDateOfBirth->format(Helper::translate(self::DB_DATE_FORMAT))
            : self::DEFAULT_DATE_OF_BIRTH;
    }

    /**
     * Sets date of birth
     *
     * @param string $sDbDateOfBirth formated for db (format 'Y-m-d')
     *
     * @since 1.2.0
     */
    public static function setDbDateOfBirth($sDbDateOfBirth)
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');
        $aDynvalues['dateOfBirth'] = '';

        if ($sDbDateOfBirth !== self::DEFAULT_DATE_OF_BIRTH) {
            $oDateOfBirth = DateTime::createFromFormat(self::DB_DATE_FORMAT, $sDbDateOfBirth);

            if ($oDateOfBirth) {
                $aDynvalues['dateOfBirth'] =
                    $oDateOfBirth->format(Helper::translate('wd_date_format_php_code'));
            }
        }

        $oSession->setVariable('dynvalue', $aDynvalues);
    }

    /**
     * Returns true if a valid date of birth is available
     *
     * @return bool
     *
     * @since 1.2.0
     */
    public static function isDateOfBirthSet()
    {
        return self::getDbDateOfBirth() !== self::DEFAULT_DATE_OF_BIRTH;
    }

    /**
     * Returns true if user is min $iAge years old, false if not or date of birth is not set
     *
     * @param int $iAge
     *
     * @return bool
     *
     * @since 1.2.0
     */
    public static function isUserOlderThan($iAge)
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');

        $oDateOfBirth =
            DateTime::createFromFormat(Helper::translate('wd_date_format_php_code'), $aDynvalues['dateOfBirth']);

        if (!$oDateOfBirth) {
            return false;
        }

        $oToday = new DateTime();
        $oDateInterval = $oDateOfBirth->diff($oToday);

        return $oDateInterval->invert === 0 && $oDateInterval->y >= $iAge;
    }

    /**
     * Returns phone
     *
     * @return string
     *
     * @since 1.2.0
     */
    public static function getPhone()
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');
        $sPhone = $aDynvalues['phone'];

        return $sPhone ? $sPhone : '';
    }

    /**
     * Sets the phone number
     *
     * @param string $sPhone
     *
     * @since 1.2.0
     */
    public static function setPhone($sPhone)
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');
        $aDynvalues['phone'] = $sPhone;

        $oSession->setVariable('dynvalue', $aDynvalues);
    }

    /**
     * Returns true if a valid phone number is available or not needed
     *
     * @return bool
     *
     * @since 1.2.0
     */
    public static function isPhoneValid()
    {
        return self::getPhone() !== '';
    }

    /**
     * Returns saveCheckoutFields
     *
     * @return string
     *
     * @since 1.2.0
     */
    public static function getSaveCheckoutFields()
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');
        return $aDynvalues['saveCheckoutFields'];
    }

    /**
     * Sets the saveCheckoutFields flag
     *
     * @param int $iSave value 1 if checkout data should be saved 0 if not
     *
     * @since 1.2.0
     */
    public static function setSaveCheckoutFields($iSave)
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');
        $aDynvalues['saveCheckoutFields'] = $iSave;

        $oSession->setVariable('dynvalue', $aDynvalues);
    }
}
