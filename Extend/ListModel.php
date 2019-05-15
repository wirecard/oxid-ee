<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend;

/**
 * @inheritdoc
 *
 * @mixin \OxidEsales\Eshop\Core\Model\ListModel
 *
 * @since 1.0.0
 */
class ListModel extends ListModel_parent
{

    /**
     * Function for loading the list including inactive items
     *
     * @return Wirecard\Oxid\Extend\ListModel
     *
     * @since 1.0.0
     */
    public function getListIncludingInactive()
    {
        $oListObject = $this->getBaseObject();
        $sFieldList = $oListObject->getSelectFields();
        $sQuery = 'select ' . $sFieldList . ' from ' . $oListObject->getViewName();
        $this->selectString($sQuery);

        return $this;
    }
}
