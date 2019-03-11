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

/**
 * Util functions
 */
class Helper
{
    /**
     * Gets the translation for a given key.
     *
     * @param string $sKey
     * @return string
     */
    public static function translate(string $sKey): string
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
     * @return string
     */
    public static function createDeviceFingerprint(string $sMaid, string $sSessionId = null): string
    {
        return $sMaid . '_' . $sSessionId;
    }

    /**
     * Check if payment id is a Wirecard id i.e. wdpaypal
     *
     * @param string $sPaymentId
     * @return bool
     */
    public static function isWirecardPaymentMethod($sPaymentId): bool
    {
        return strpos($sPaymentId, "wd") === 0;
    }

    /**
     * Returns a list of available payments.
     *
     * @return array
     */
    public static function getPayments(): array
    {
        $oPaymentList = oxNew(ListModel::class);
        $oPaymentList->init(Payment::class);

        return $oPaymentList->getList()->getArray();
    }

    /**
     * Returns a list of available payments added by the plugin.
     *
     * @return array
     */
    public static function getPluginPayments(): array
    {
        return array_filter(self::getPayments(), function ($oPayment) {
            return $oPayment->oxpayments__wdoxidee_iswirecard->value;
        });
    }

    /**
     * Converts a string to float while acknowledging different number formats with diferent decimal points and
     * thousand separators.
     *
     * @param string $sNumber
     * @return float
     */
    public static function getFloatFromString(string $sNumber): float
    {
        return (float) preg_replace('/\.(?=.*\.)/', '', str_replace(',', '.', $sNumber));
    }
}
