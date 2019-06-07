<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use Wirecard\Oxid\Model\Transaction;

/**
 * Class PostProcessingHelper
 *
 * @since 1.2.0
 */
class PostProcessingHelper
{
    /**
     * Get the order items from the basket
     *
     * @param Transaction $oTransaction
     *
     * @return array
     *
     * @since 1.2.0
     */
    public static function getOrderItems($oTransaction)
    {
        return $oTransaction->getBasket()->mappedProperties()["order-item"];
    }

    /**
     *
     * Calculate the actual quantity of the order items
     *
     * @param $aOrderItems
     * @param $aChildOrderItems
     */
    public static function recalculateOrderItems(&$aOrderItems, $aChildOrderItems)
    {
        foreach ($aOrderItems as &$aOrderItem)
        {
            foreach ($aChildOrderItems as $aChildOrderItem) {
                if ($aOrderItem['article-number'] === $aChildOrderItem['article-number']) {
                    $aOrderItem['quantity'] = $aOrderItem['quantity'] - $aChildOrderItem['quantity'];
                    continue;
                }
            }
        }
    }
}
