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
 * Controls the view for the order details tab.
 *
 * @since 1.0.1
 */
class TransactionTabResponseDetails extends TransactionTab
{
    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.0.1
     */
    protected function _getData(): array
    {
        if (!$this->oResponseMapper) {
            return [];
        }

        return $this->oResponseMapper->getDataReadable();
    }
}
