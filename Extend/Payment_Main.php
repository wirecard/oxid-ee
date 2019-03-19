<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend;

use \OxidEsales\Eshop\Core\Registry;

/**
 * Class Payment_Main
 *
 */
class Payment_Main extends Payment_Main_parent
{

    /**
     * Executes parent::render(), creates a currency array
     *
     * @return string
     *
     */
    public function render()
    {
        $sTemplate = parent::render();
        $oConfig = Registry::getConfig();
        if ($this->_aViewData["oxid"] == "wdcreditcard") {
            $this->_aViewData["currencies"] = $oConfig->getCurrencyArray();
        }
        return $sTemplate;
    }
}
