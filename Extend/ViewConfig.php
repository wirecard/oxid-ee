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

use OxidEsales\Eshop\Application\Model\Shop;

use Wirecard\Oxid\Core\Helper;

/**
 * Extends the OXID ViewConfig
 *
 * @mixin \OxidEsales\Eshop\Core\ViewConfig
 *
 * @since 1.0.0
 */
class ViewConfig extends ViewConfig_parent
{

    /**
     *
     * Returns the device id for the fraud protection script in out/blocks/profiling_tags.tpl
     *
     * @param string $sMaid
     * @return string
     *
     * @since 1.0.0
     */
    public function getModuleDeviceId($sMaid)
    {
        return Helper::createDeviceFingerprint($sMaid, $this->getSessionId());
    }

    /**
     * Returns HTML for the [{oxinputhelp}] Smarty function.
     *
     * @see Helper::getInputHelpHtml()
     * @param string $sText
     * @return string
     *
     * @since 1.0.0
     */
    public function getInputHelpHtml($sText)
    {
        return Helper::getInputHelpHtml($sText);
    }

    /**
     * Returns path to the module asset file
     *
     * @param string $sPath
     * @return string
     *
     * @since 1.0.0
     */
    public function getPaymentGatewayUrl($sPath)
    {
        return $this->getModuleUrl(Helper::MODULE_ID, $sPath);
    }

    /**
     * Check if passed module is Payment Gateway Module id
     *
     * @param string $sModuleId
     * @return bool
     *
     * @since 1.0.0
     */
    public function isThisModule($sModuleId)
    {
        return Helper::isThisModule($sModuleId);
    }

    /**
     * Returns shop
     *
     * @return Shop
     *
     * @since 1.0.1
     */
    public function getShop()
    {
        return Helper::getShop();
    }
}
