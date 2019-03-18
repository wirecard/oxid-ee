<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 *
 */

namespace Wirecard\Oxid\Extend;

use \Wirecard\Oxid\Core\Helper;

/**
 * Extends the OXID ViewConfig
 */
class ViewConfig extends ViewConfig_parent
{

    public function getWirecardDeviceId(string $sMaid)
    {
        return Helper::createDeviceId($sMaid, $this->getSessionId());
    }

    public function isWirecardPaymentMethod($sPaymentId)
    {
        //FIXME: check for all payment methods
        return strcmp($sPaymentId, "wdpaypal") == 0;
    }
}
