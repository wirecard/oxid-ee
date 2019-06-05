<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin\Order;

/**
 * Controls the view for the order transaction details tab.
 *
 * @since 1.0.0
 */
class OrderTabTransactionDetails extends OrderTab
{
    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.0.0
     */
    protected function _getData()
    {
        if (!$this->_oResponseMapper) {
            return [];
        }

        return $this->_oResponseMapper->getDataReadable();
    }
}
