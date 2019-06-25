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

use OxidEsales\Eshop\Application\Model\Address;
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
    const SESSION_VAR_DELIVERY_ADDRESS = 'deladrid';

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
     * @param string $sPaymentMethodName
     *
     * @return string date of birth formatted for db (format 'Y-m-d')
     *
     * @since 1.2.0
     */
    public static function getDbDateOfBirth($sPaymentMethodName)
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');
        $sDateOfBirth = $aDynvalues['dateOfBirth' . $sPaymentMethodName];
        //To change the format string add a translation for another language on phrase app
        $oDateOfBirth = DateTime::createFromFormat(Helper::translate('wd_date_format_php_code'), $sDateOfBirth);

        return $oDateOfBirth
            ? $oDateOfBirth->format(Helper::translate(self::DB_DATE_FORMAT))
            : self::DEFAULT_DATE_OF_BIRTH;
    }

    /**
     * Sets date of birth
     *
     * @param string $sDbDateOfBirth     formatted for db (format 'Y-m-d')
     * @param string $sPaymentMethodName the name of the payment method
     *
     * @since 1.2.0
     */
    public static function setDbDateOfBirth($sDbDateOfBirth, $sPaymentMethodName)
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');
        $aDynvalues['dateOfBirth' . $sPaymentMethodName] = '';

        if ($sDbDateOfBirth !== self::DEFAULT_DATE_OF_BIRTH) {
            $oDateOfBirth = DateTime::createFromFormat(self::DB_DATE_FORMAT, $sDbDateOfBirth);

            if ($oDateOfBirth) {
                $aDynvalues['dateOfBirth' . $sPaymentMethodName] =
                    $oDateOfBirth->format(Helper::translate('wd_date_format_php_code'));
            }
        }

        $oSession->setVariable('dynvalue', $aDynvalues);
    }

    /**
     * Returns true if user is min $iAge years old, false if not or date of birth is not set
     *
     * @param int    $iAge
     * @param string $sPaymentMethodName
     *
     * @return bool
     *
     * @throws \Exception
     *
     * @since 1.2.0
     */
    public static function isUserOlderThan($iAge, $sPaymentMethodName)
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');

        $oDateOfBirth = DateTime::createFromFormat(
            Helper::translate('wd_date_format_php_code'),
            $aDynvalues['dateOfBirth' . $sPaymentMethodName]
        );

        if (!$oDateOfBirth) {
            return false;
        }

        $oToday = new DateTime();
        $oDateInterval = $oDateOfBirth->diff($oToday);

        return $oDateInterval->invert === 0 && $oDateInterval->y >= $iAge;
    }

    /**
     * Returns true if a valid date of birth is available
     *
     * @param string $sPaymentMethodName
     *
     * @return bool
     *
     * @since 1.2.0
     */
    public static function isDateOfBirthSet($sPaymentMethodName)
    {
        return self::getDbDateOfBirth($sPaymentMethodName) !== self::DEFAULT_DATE_OF_BIRTH;
    }

    /**
     * Returns phone
     *
     * @param string $sPaymentMethodName
     *
     * @return string
     *
     * @since 1.2.0
     */
    public static function getPhone($sPaymentMethodName)
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');
        $sPhone = $aDynvalues['phone' . $sPaymentMethodName];

        return $sPhone ? $sPhone : '';
    }

    /**
     * Sets the phone number
     *
     * @param string $sPhone
     * @param string $sPaymentMethodName
     *
     * @since 1.2.0
     */
    public static function setPhone($sPhone, $sPaymentMethodName)
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');
        $aDynvalues['phone' . $sPaymentMethodName] = $sPhone;

        $oSession->setVariable('dynvalue', $aDynvalues);
    }

    /**
     * Returns true if a valid phone number is available or not needed
     *
     * @param string $sPaymentMethodName
     *
     * @return bool
     *
     * @since 1.2.0
     */
    public static function isPhoneValid($sPaymentMethodName)
    {
        return self::getPhone($sPaymentMethodName) !== '';
    }

    /**
     * Returns saveCheckoutFields
     *
     * @param string $sPaymentMethodName
     *
     * @return string
     *
     * @since 1.2.0
     */
    public static function getSaveCheckoutFields($sPaymentMethodName)
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');
        return $aDynvalues['saveCheckoutFields' . $sPaymentMethodName];
    }

    /**
     * Sets the saveCheckoutFields flag
     *
     * @param int    $iSave              value 1 if checkout data should be saved 0 if not
     * @param string $sPaymentMethodName the name of the payment method
     *
     * @since 1.2.0
     */
    public static function setSaveCheckoutFields($iSave, $sPaymentMethodName)
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');
        $aDynvalues['saveCheckoutFields' . $sPaymentMethodName] = $iSave;

        $oSession->setVariable('dynvalue', $aDynvalues);
    }

    /**
     * Returns the ID for the billing country the user has set. If no country was set, null will be returned.
     *
     * @return string|null
     *
     * @since 1.2.0
     */
    public static function getBillingCountryId()
    {
        $oSession = Registry::getSession();

        return $oSession->getUser()->oxuser__oxcountryid->value ?? null;
    }

    /**
     * Returns the ID for the shipping country the user has set. If no country was set (or no explicit shipping address
     * was set), null will be returned.
     *
     * @return string|null
     *
     * @since 1.2.0
     */
    public static function getShippingCountryId()
    {
        $oSession = Registry::getSession();

        if ($oSession->getVariable(self::SESSION_VAR_DELIVERY_ADDRESS)) {
            $oShippingAddress = oxNew(Address::class);
            $oShippingAddress->load($oSession->getVariable(self::SESSION_VAR_DELIVERY_ADDRESS));

            return $oShippingAddress->oxaddress__oxcountryid->value ?? null;
        }

        return null;
    }

    /**
     * Set the company name in the user's session
     *
     * @param string $sCompanyName
     *
     * @since 1.3.0
     */
    public static function setCompanyName($sCompanyName)
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');
        $aDynvalues['wdCompanyName'] = $sCompanyName;

        $oSession->setVariable('dynvalue', $aDynvalues);
    }

    /**
     * Get the company name saved in the user's session
     *
     * @return string
     *
     * @since 1.3.0
     */
    public static function getCompanyName()
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');
        return $aDynvalues['wdCompanyName'];
    }

    /**
     * Check if company name is set in the user's session
     *
     * @return bool
     *
     * @since 1.3.0
     */
    public static function isCompanyNameSet()
    {
        return !is_null(self::getCompanyName());
    }
}
