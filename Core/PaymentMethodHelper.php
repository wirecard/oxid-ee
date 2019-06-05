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

use Wirecard\Oxid\Extend\Model\Payment;

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

        $oSmarty->assign('sAccountHolder', SessionHelper::getAccountHolder());
        $oSmarty->assign('oShop', $oShop);
        $oSmarty->assign('oPayment', $oPayment);
        $oSmarty->assign('sMandateId', self::getMandate($sSessionChallenge)->mappedProperties()['mandate-id']);
        $oSmarty->assign('sIban', SessionHelper::getIban());
        $oSmarty->assign('sBic', SessionHelper::getBic());
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
}
