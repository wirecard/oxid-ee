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

/**
 * Extends the OXID Language class
 *
 * @mixin \OxidEsales\Eshop\Core\Language
 *
 * @since 1.0.1
 */
class Language extends Language_parent
{
    /**
     * @var int
     *
     * @since 1.0.1
     */
    private $_iFallbackId = null;

    /**
     * @inheritdoc
     *
     * Provides a fallback language string if the chosen language is not available for the plugin
     *
     * @param string $sStringToTranslate Initial string
     * @param int    $iLang              optional language number
     * @param bool   $blAdminMode        force to load language constant from admin/shops language file
     *
     * @return string
     *
     * @since 1.0.1
     */
    public function translateString($sStringToTranslate, $iLang = null, $blAdminMode = null)
    {
        if (strpos($sStringToTranslate, 'wdpg_') === 0) {
            $sParentString = parent::translateString($sStringToTranslate, $iLang, $blAdminMode);

            if ($sParentString === $sStringToTranslate) {
                return $this->_getFallbackString($sStringToTranslate);
            }

            return $sParentString;
        }

        return parent::translateString($sStringToTranslate, $iLang, $blAdminMode);
    }

    /**
     * Returns the fallback language's string for the provided key
     *
     * @param string $sStringToTranslate
     *
     * @return string
     *
     * @since 1.0.1
     */
    private function _getFallbackString($sStringToTranslate)
    {
        if ($this->_iFallbackId === null) {
            foreach ($this->getLanguageArray() as $oLanguage) {
                if ($oLanguage->abbr === 'en') {
                    $this->_iFallbackId = $oLanguage->id;
                    break;
                }
            }
        }

        return parent::translateString($sStringToTranslate, $this->_iFallbackId);
    }
}
