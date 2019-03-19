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
 *
 * @extends \OxidEsales\Eshop\Core\ViewConfig
 */
class View_Config extends View_Config_parent
{

    /**
     *
     * Returns the device id for the fraud protection script in out/blocks/profiling_tags.tpl
     *
     * @param string $sMaid
     * @return string
     */
    public function getWirecardDeviceId(string $sMaid): string
    {
        return Helper::createDeviceFingerprint($sMaid, $this->getSessionId());
    }

    /**
     *
     * Checks if payment id is a module id i.e. wdpaypal
     *
     * @param string $sPaymentId
     * @return bool
     */
    public function isModulePaymentMethod($sPaymentId): bool
    {
        return Helper::isModulePaymentMethod($sPaymentId);
    }
}
