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
 */
class OrderTabTransactionDetails extends OrderTab
{
    /**
     * @inheritdoc
     *
     * @return array
     */
    protected function _getData(): array
    {
        if (!$this->oResponseMapper) {
            return array();
        }

        $aTransactionResponse = $this->oResponseMapper->getData();

        $aSortKeys = [
            'payment-methods.0.name',
            'order-number',
            'request-id',
            'transaction-id',
            'transaction-state',
            'statuses.0.provider-transaction-id'
        ];

        $aRestOfKeys = array_diff(array_keys($aTransactionResponse), $aSortKeys);
        $aSortedKeys = array_merge($aSortKeys, $aRestOfKeys);

        $aList = array();
        foreach ($aSortedKeys as $sKey) {
            $aList[] = [
                'title' => $sKey,
                'value' => $aTransactionResponse[$sKey] ?? null
            ];
        }

        return $aList;
    }
}
