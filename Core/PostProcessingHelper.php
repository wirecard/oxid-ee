<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use Wirecard\Oxid\Model\PaymentMethod;
use Wirecard\Oxid\Model\PayolutionInvoicePaymentMethod;
use Wirecard\Oxid\Model\RatepayInvoicePaymentMethod;
use Wirecard\Oxid\Model\Transaction;

/**
 * Class PostProcessingHelper
 *
 * @since 1.2.0
 */
class PostProcessingHelper
{

    /**
     * Returns whether to use order items or amount in the panel
     *
     * @param Transaction $oTransaction
     *
     * @return bool
     *
     * @since 1.2.0
     */
    public static function shouldUseOrderItems($oTransaction)
    {
        return in_array($oTransaction->getPaymentType(), [
            RatepayInvoicePaymentMethod::getName(true),
            PayolutionInvoicePaymentMethod::getName(true),
        ]);
    }

    /**
     * Filters the returned post processing actions for a payment method.
     * It is possible to do payment method specific modifications in this method.
     *
     * @param array         $aPossibleOperations
     * @param PaymentMethod $oPaymentMethod
     * @param Transaction   $oTransaction
     *
     * @return array
     *
     * @since 1.2.0
     */
    public static function filterPostProcessingActions($aPossibleOperations, $oPaymentMethod, $oTransaction)
    {
        foreach ($aPossibleOperations as $sActionKey => $sDisplayValue) {
            $oPgTransaction = $oPaymentMethod->getPostProcessingTransaction($sActionKey, $oTransaction);
            $oPayment = PaymentMethodHelper::getPaymentById(
                PaymentMethod::getOxidFromSDKName($oPgTransaction->getConfigKey())
            );

            if (!$oPayment->oxpayments__oxactive->value) {
                unset($aPossibleOperations[$sActionKey]);
            }
        }

        return $aPossibleOperations;
    }

    /**
     * Map order items to tableable items
     *
     * @param Transaction $oTransaction
     *
     * @return array
     *
     * @since 1.2.0
     */
    public static function getMappedTableOrderItems($oTransaction)
    {
        $aOrderItems = self::_getOrderItems($oTransaction);
        self::_recalculateQuantity($oTransaction, $aOrderItems);
        $aMappedItems = array_map(function ($aOrderItem) {

            $sInputFileds = '<input type="number" value="' . $aOrderItem['quantity'] . '" name="quantity[]" min="0"' .
                ' max="' . $aOrderItem['quantity'] . '"/>' .
                '<input type="hidden" value="' . $aOrderItem['article-number'] . '" name="article-number[]" />';

            return [
                ['text' => $aOrderItem['article-number']],
                ['text' => $aOrderItem['name']],
                ['text' => $aOrderItem['amount']['value']],
                ['text' => $sInputFileds],
            ];
        }, $aOrderItems);
        return $aMappedItems;
    }

    /**
     * Get the order items from the basket
     *
     * @param Transaction $oTransaction
     *
     * @return array
     *
     * @since 1.2.0
     */
    private static function _getOrderItems($oTransaction)
    {
        return $oTransaction->getBasket()->mappedProperties()["order-item"];
    }

    /**
     * Recalculate the Quantity of the items
     *
     * @param Transaction $oTransaction
     * @param array       $aOrderItems
     *
     * @since 1.2.0
     */
    private static function _recalculateQuantity($oTransaction, &$aOrderItems)
    {
        $aChildTransactions = $oTransaction->getChildTransactions();

        foreach ($aChildTransactions as $oChildTransaction) {
            self::_recalculateOrderItems(
                $aOrderItems,
                self::_getOrderItems($oChildTransaction)
            );
        }
    }

    /**
     * Calculate the actual quantity of the order items
     *
     * @param array $aOrderItems
     * @param array $aChildOrderItems
     *
     * @since 1.2.0
     */
    private static function _recalculateOrderItems(&$aOrderItems, $aChildOrderItems)
    {
        foreach ($aOrderItems as &$aOrderItem) {
            foreach ($aChildOrderItems as $aChildOrderItem) {
                if ($aOrderItem['article-number'] === $aChildOrderItem['article-number']) {
                    $aOrderItem['quantity'] = $aOrderItem['quantity'] - $aChildOrderItem['quantity'];
                    continue;
                }
            }
        }
    }
}
