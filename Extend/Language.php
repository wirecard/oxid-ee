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
    const TRANSLATION_KEY_PREFIX = 'wdpg_';
    const FALLBACK_LANGUAGE_ABBR = 'en';

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
     * @param string $sStringToTranslate
     * @param int    $iLanguageId
     * @param bool   $bAdminMode         force to load language constant from admin/shops language file
     *
     * @return string
     *
     * @since 1.0.1
     */
    public function translateString($sStringToTranslate, $iLanguageId = null, $bAdminMode = null)
    {
        $sParentString = parent::translateString($sStringToTranslate, $iLanguageId, $bAdminMode);

        if (!$this->isTranslated() && strpos($sStringToTranslate, self::TRANSLATION_KEY_PREFIX) === 0) {
            return $this->_getFallbackString($sStringToTranslate, $bAdminMode);
        }

        return $sParentString;
    }

    /**
     * Returns the fallback language's string for the provided key
     *
     * @param string $sStringToTranslate
     * @param bool   $bAdminMode
     *
     * @return string
     *
     * @since 1.0.1
     */
    private function _getFallbackString($sStringToTranslate, $bAdminMode = null)
    {
        if ($this->_iFallbackId === null) {
            foreach ($this->getLanguageArray() as $oLanguage) {
                if ($oLanguage->abbr === self::FALLBACK_LANGUAGE_ABBR) {
                    $this->_iFallbackId = $oLanguage->id;
                    break;
                }
            }
        }

        return parent::translateString($sStringToTranslate, $this->_iFallbackId, $bAdminMode);
    }
}
