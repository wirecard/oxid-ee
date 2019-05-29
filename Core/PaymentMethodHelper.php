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

use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Model\ListModel;
use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Extend\Model\Order;
use Wirecard\PaymentSdk\Entity\Mandate;

use Wirecard\Oxid\Extend\Model\Payment;

/**
 * Helper class to handle payment methods
 *
 * @since 1.0.0
 */
class PaymentMethodHelper
{
    const MAX_MANDATE_ID_LENGTH = 35;
    const DB_DATE_FORMAT = 'Y-m-d';

    /**
     * Returns a payment with the selected id.
     *
     * @param string $sPaymentId
     *
     * @return Payment
     *
     * @since 1.0.0
     */
    public static function getPaymentById($sPaymentId)
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->load($sPaymentId);

        return $oPayment;
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
     * Return array for currency select options
     *
     * @return array
     *
     * @since 1.2.0
     */
    public static function getCurrencyOptions()
    {
        $aCurrencies = Registry::getConfig()->getCurrencyArray();
        $aOptions = [];

        foreach ($aCurrencies as $oCurrency) {
            $aOptions[$oCurrency->name] = $oCurrency->name;
        }

        return $aOptions;
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
     * Generates a Mandate for SEPA transactions
     *
     * @param int $iOrderNumber
     *
     * @return Mandate
     *
     * @since 1.1.0
     */
    public static function getMandate($iOrderNumber)
    {
        $sTime = (string) time();
        $iLength = self::MAX_MANDATE_ID_LENGTH - 1 - strlen($sTime);
        return new Mandate(substr($iOrderNumber, 0, $iLength) . '-' . $sTime);
    }

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
     * @return string date of birth formated for db (format 'Y-m-d')
     *
     * @since 1.2.0
     */
    public static function getDbDateOfBirth()
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');

        $oDateOfBirth =
            DateTime::createFromFormat(Helper::translate('wd_birthdate_format_php_code'), $aDynvalues['dateOfBirth']);
        return $oDateOfBirth ? $oDateOfBirth->format(Helper::translate(self::DB_DATE_FORMAT)) : '';
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

        if ($sDbDateOfBirth !== '0000-00-00') {
            $sDbFormatedDateOfBirth = DateTime::createFromFormat(self::DB_DATE_FORMAT, $sDbDateOfBirth);
            $aDynvalues['dateOfBirth'] =
                $sDbFormatedDateOfBirth->format(Helper::translate('wd_birthdate_format_php_code'));
        }

        $oSession->setVariable('dynvalue', $aDynvalues);
    }

    /**
     * Returns true if user is 18 or older
     *
     * @return bool
     *
     * @since 1.2.0
     */
    public static function isUserEighteen()
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');

        $oDateOfBirth =
            DateTime::createFromFormat(Helper::translate('wd_birthdate_format_php_code'), $aDynvalues['dateOfBirth']);
        $oToday = new DateTime();
        $oDateInterval = $oDateOfBirth->diff($oToday);
        return $oDateInterval->invert === 0 && $oDateInterval->y >= 18;
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
        return $aDynvalues['phone'];
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

    /**
     * Generates SEPA mandate html body
     *
     * @param Basket $oBasket
     * @param User   $oUser
     *
     * @return string
     *
     * @since 1.1.0
     */
    public static function getSepaMandateHtml($oBasket, $oUser)
    {
        $sSessionChallenge = Helper::getSessionChallenge();
        $oPayment = oxNew(Payment::class);
        $oPayment->load($oBasket->getPaymentId());
        $oShop = Helper::getShop();
        $sCreditorName = self::prepareCreditorName();

        $oSmarty = Registry::getUtilsView()->getSmarty();

        $oSmarty->assign('sAccountHolder', self::getAccountHolder());
        $oSmarty->assign('oShop', $oShop);
        $oSmarty->assign('oPayment', $oPayment);
        $oSmarty->assign('sMandateId', self::getMandate($sSessionChallenge)->mappedProperties()['mandate-id']);
        $oSmarty->assign('sIban', self::getIban());
        $oSmarty->assign('sBic', self::getBic());
        $oSmarty->assign('sConsumerCity', $oUser->oxuser__oxcity->value);
        $oSmarty->assign('sDate', date('d.m.Y', time()));
        $oSmarty->assign('sCreditorName', $sCreditorName);

        $sCustomSepaMandate = str_replace(
            '%creditorName%',
            $sCreditorName,
            $oPayment->oxpayments__wdoxidee_sepamandatecustom
        );

        $oSmarty->assign('sCustomSepaMandate', $sCustomSepaMandate);

        return $oSmarty->fetch('sepa_mandate.tpl');
    }

    /**
     * Prepares creditor name depending on information available in the shop settings
     *
     * @return string
     *
     * @since 1.1.0
     */
    public static function prepareCreditorName()
    {
        $oShop = Helper::getShop();
        $sCreditorName = trim($oShop->oxshops__oxfname . ' ' . $oShop->oxshops__oxlname);

        return $sCreditorName ? $sCreditorName : $oShop->oxshops__oxcompany;
    }

    /**
     * Checks the user data if mandatory fields are set correctly and saves them if needed
     *
     * @since 1.2.0
     */
    public static function checkUserDataInput()
    {
        if (PaymentMethodHelper::getDbDateOfBirth() === ''
            || !PaymentMethodHelper::isUserEighteen()) {
            $sShopBaseUrl = Registry::getConfig()->getShopUrl();
            $sLanguageCode = Registry::getLang()->getBaseLanguage();

            $aParams = [
                'lang' => $sLanguageCode,
                'cl' => 'payment',
                'payerror' => Order::ORDER_STATE_INVALIDPAYMENT,
            ];
            $sParamStr = http_build_query($aParams);
            $sNewUrl = $sShopBaseUrl . 'index.php?' . $sParamStr;

            Registry::getUtils()->redirect($sNewUrl);
        }

        if (PaymentMethodHelper::getSaveCheckoutFields() === '1') {
            $oUser = Registry::getSession()->getUser();
            $oUser->oxuser__oxbirthdate = new Field(PaymentMethodHelper::getDbDateOfBirth());
            $oUser->oxuser__oxfon = new Field(PaymentMethodHelper::getPhone());
            $oUser->save();
        }
    }
}
