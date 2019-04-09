<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin;

/**
 * Controls the view for the shipping transaction tab.
 */
class TransactionTabShipping extends TransactionTab
{
    /**
     * @inheritdoc
     *
     * @return array
     */
    protected function _getData(): array
    {
        return $this->_getListDataFromArray($this->oResponseMapper->getShipping());
    }
}
