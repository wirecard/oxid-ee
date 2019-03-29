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
 * Controls the view for the data transaction tab.
 */
class TransactionTabTransactionDetails extends TransactionTab
{
    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getListData(): array
    {
        return $this->_getListDataFromArray($this->oResponseMapper->getTransactionDetails());
    }
}
