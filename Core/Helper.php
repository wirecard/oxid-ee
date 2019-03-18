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


class Helper
{
    public static function createDeviceId(string $sMaid, string $sSessionId)
    {
        return $sMaid . '_' . $sSessionId;
    }
}
