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
 * Controls the view for the account holder transaction tab.
 */
class TransactionTabAccountHolder extends TransactionTab
{
    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getListData(): array
    {
        return $this->_getListDataFromArray($this->oResponseMapper->getAccountHolder());
    }
}
