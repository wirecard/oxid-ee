<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 *
 */

namespace Wirecard\Oxid\Core;

/**
 * Util functions
 */
class Helper
{
    /**
     *
     * Create a Fingerprint for the Device.fingerprint fraud protection
     *
     * also used in out/blocks/profiling_tags.tpl for the session id
     *
     * @param string $sMaid
     * @param string $sSessionId
     * @return string
     */
    public static function createDeviceId(string $sMaid, string $sSessionId): string
    {
        return $sMaid . '_' . $sSessionId;
    }

    /**
     *
     * check id payment id is a Wirecard id i.e. wdpaypal
     *
     * @param $sPaymentId
     * @return bool
     */
    public static function isWirecardPaymentMethod($sPaymentId): bool
    {
        return strpos($sPaymentId, "wd") === 0;
    }
}
