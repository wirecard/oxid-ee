<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin\Transaction;

/**
 * Controls the view for the account holder transaction tab.
 *
 * @since 1.0.0
 */
class TransactionTabAccountHolder extends TransactionTab
{
    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.0.0
     */
    protected function _getData(): array
    {
        return $this->_getListDataFromArray($this->oResponseMapper->getAccountHolder());
    }
}
