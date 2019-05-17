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
        $iLength = self::MAX_MANDATE_ID_LENGTH - 1 - strlen((string) time());
        return new Mandate(substr($iOrderNumber, 0, $iLength) . '-' . time());
    }
}
