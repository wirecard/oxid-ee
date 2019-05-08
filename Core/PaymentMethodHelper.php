<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use OxidEsales\Eshop\Core\Model\ListModel;
use OxidEsales\Eshop\Core\Registry;

use Wirecard\PaymentSdk\Entity\Mandate;

use Wirecard\PaymentSdk\Entity\Mandate;

use Wirecard\Oxid\Extend\Model\Payment;
use Wirecard\Oxid\Core\Helper;

/**
 * Helper class to handle payment methods
 *
 * @since 1.0.0
 */
class PaymentMethodHelper
{
    const MAX_MANDATE_ID_LENGTH = 35;
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
    * @since 1.0.1
    */
    public static function getMandate($iOrderNumber)
    {
        $iLength = self::MAX_MANDATE_ID_LENGTH - 1 - strlen((string) time());
        return new Mandate(substr($iOrderNumber, 0, $iLength) . '-' . time());
    }

    /**
     * Returns account holder for Sepa Direct Debit
     *
     * @return string
     *
     * @since 1.0.1
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
     * @since 1.0.1
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
     * @since 1.0.1
     */
    public function getBic()
    {
        $oSession = Registry::getConfig()->getSession();
        $aDynvalues = $oSession->getVariable('dynvalue');
        return $aDynvalues['bic'];
    }

    /**
     * Generates sepa mandate text
     *
     * @param Basket $oBasket
     *
     * @return string
     *
     * @since 1.0.1
     */
    public static function generateSepaMandate($oBasket)
    {
        $oShopAddress = Helper::getShopAddress();
        $iOrderNumber = Helper::getSessionChallenge();
        $oPayment = oxNew(Payment::class);
        $oPayment->load($oBasket->getPaymentId());

        $sSepaMandateHeader = self::generateSepaMandateHeader($oShopAddress, $oPayment, $iOrderNumber);
        $sSepaMandateFooter = self::generateSepaMandateFooter($oShopAddress);
        $sSepaMandateMain = self::generateSepaMandateMainText($oShopAddress);

        if ($oPayment->oxpayments__wdoxidee_sepamandatecustom->value) {
            $sSepaMandateMain = $oPayment->oxpayments__wdoxidee_sepamandatecustom->value;
        }

        return $sSepaMandateHeader . $sSepaMandateMain . $sSepaMandateFooter;
    }

    /**
     * Generates sepa mandate header text
     *
     * @param Shop    $oShopAddress
     *
     * @param Payment $oPayment
     *
     * @param integer $iOrderNumber
     *
     * @return string
     *
     * @since 1.0.1
     */
    public static function generateSepaMandateHeader($oShopAddress, $oPayment, $iOrderNumber)
    {
        return '<h3>' . Helper::translate('wd_sepa_mandate') . '</h3><hr>
            <i>' . Helper::translate('wd_creditor') . '</i><p style="margin-bottom: 30px">' .
            $oShopAddress->oxshops__oxfname . ' ' . $oShopAddress->oxshops__oxlname . ',<br>' .
            $oShopAddress->oxshops__oxstreet . '<br>' .
            $oShopAddress->oxshops__oxzip . ' ' . $oShopAddress->oxshops__oxcity . '<br>' .
            $oShopAddress->oxshops__oxcountry . '<br>' .
            Helper::translate('wd_config_creditor_id') . ' ' . $oPayment->oxpayments__wdoxidee_creditorid->value
            . '<br> ' . Helper::translate('wd_creditor_mandate_id') . ' ' .
            self::getMandate($iOrderNumber)->mappedProperties()['mandate-id'] . '</p>
            <i>' . Helper::translate('wd_debtor') . '</i><p style="margin-bottom: 30px">
            ' . Helper::translate('wd_debtor_acc_owner') . ' ' . self::getAccountHolder() . '<br>' .
            Helper::translate('wd_iban') . ' ' . self::getIban() . '</p>';
    }

    /**
     * Generates sepa mandate header text
     *
     * @param Shop $oShopAddress
     *
     * @return string
     *
     * @since 1.0.1
     */
    public static function generateSepaMandateFooter($oShopAddress)
    {
        return '<p style="margin-top: 30px">' . $oShopAddress->oxshops__oxcity . ', ' . date("d.m.Y", time()) . ' '
            . self::getAccountHolder() . '</p>';
    }

    /**
     * Generates sepa mandate main text
     *
     * @param Shop $oShopAddress
     *
     * @return string
     *
     * @since 1.0.1
     */
    public static function generateSepaMandateMainText($oShopAddress)
    {
        return '<p> ' . Helper::translate('wd_sepa_text_1') . ' ' . $oShopAddress->oxshops__oxfname . ' ' .
            $oShopAddress->oxshops__oxlname . ' ' . Helper::translate('wd_sepa_text_2') . ' ' .
            $oShopAddress->oxshops__oxfname . ' ' . $oShopAddress->oxshops__oxlname . ' ' .
            Helper::translate('wd_sepa_text_2b') . '</p>
            <p>' . Helper::translate('wd_sepa_text_3') . '</p>
            <p style="margin-bottom: 30px">' . Helper::translate('wd_sepa_text_4') . ' ' .
            $oShopAddress->oxshops__oxfname . ' ' . $oShopAddress->oxshops__oxlname . ' ' .
            Helper::translate('wd_sepa_text_5') . '</p> ';
    }
}
