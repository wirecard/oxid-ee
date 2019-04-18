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
 *
 * @since 1.0.0
 */
class OrderList extends OrderList_parent
{
    /**
     * @inheritdoc
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function render()
    {
        $oOrder = oxNew(Order::class);
        $this->setViewData($this->getViewData() + [
            'orderStates' => $oOrder::getTranslatedStates(),
        ]);

        return parent::render();
    }
}
