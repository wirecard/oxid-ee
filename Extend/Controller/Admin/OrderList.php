<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend\Controller\Admin;

use OxidEsales\Eshop\Application\Model\Order;

/**
 * Controls the order list view.
 *
 * @mixin \OxidEsales\Eshop\Application\Controller\Admin\OrderList
 */
class OrderList extends OrderList_parent
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public function render()
    {
        $oOrder = oxNew(Order::class);
        $this->_aViewData += [
            'orderStates' => $oOrder::getTranslatedStates(),
        ];

        return parent::render();
    }
}
