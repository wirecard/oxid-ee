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
        $sTime = (string) time();
        $iLength = self::MAX_MANDATE_ID_LENGTH - 1 - strlen($sTime);
        return new Mandate(substr($iOrderNumber, 0, $iLength) . '-' . $sTime);
    }

    /**
     * Returns account holder for SEPA Direct Debit
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
     * Generates SEPA mandate html body
     *
     * @param Basket $oBasket
     * @param User   $oUser
     *
     * @return string
     *
     * @since 1.0.1
     */
    public static function getSepaMandateHtml($oBasket, $oUser)
    {
        $iOrderNumber = Helper::getSessionChallenge();
        $oPayment = oxNew(Payment::class);
        $oPayment->load($oBasket->getPaymentId());
        $oShop = Helper::getShop();

        $oSmarty = Registry::getUtilsView()->getSmarty();

        $oSmarty->assign('sAccountHolder', self::getAccountHolder());
        $oSmarty->assign('oShop', $oShop);
        $oSmarty->assign('oPayment', $oPayment);
        $oSmarty->assign('sMandateId', self::getMandate($iOrderNumber)->mappedProperties()['mandate-id']);
        $oSmarty->assign('sIban', self::getIban());
        $oSmarty->assign('sBic', self::getBic());
        $oSmarty->assign('sConsumerCity', $oUser->oxuser__oxcity->value);
        $oSmarty->assign('sDate', date('d.m.Y', time()));

        $sCustom = str_replace(
            '%creditorName%',
            $oShop->oxshops__oxfname . ' ' . $oShop->oxshops__oxlname,
            $oPayment->oxpayments__wdoxidee_sepamandatecustom
        );

        $oSmarty->assign('sCustom', $sCustom);

        return $oSmarty->fetch('sepa_mandate.tpl');
    }
}
