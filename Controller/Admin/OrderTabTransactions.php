<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Model\Transaction;

/**
 * Controls the view for the order transaction tab.
 */
class OrderTabTransactions extends OrderTab
{
    /**
     * @inheritdoc
     */
    protected $_sThisTemplate = 'tab_table.tpl';

    /**
     * Returns an array to populate the table body.
     *
     * @param array $aTransactions
     * @param int   $level
     * @return array
     */
    protected function _getBodyData(array $aTransactions, int $level = 0)
    {
        $aRowData = [];

        foreach ($aTransactions as $oTransaction) {
            $aRowData = array_merge(
                $aRowData,
                [
                    [
                        [
                            'text' => $oTransaction->wdoxidee_ordertransactions__transactionid->value,
                            'indent' => $level,
                        ],
                        [
                            'text' => $oTransaction->wdoxidee_ordertransactions__requestid->value,
                        ],
                        [
                            'text' => $oTransaction->wdoxidee_ordertransactions__action->value,
                        ],
                        [
                            'text' => $oTransaction->wdoxidee_ordertransactions__type->value,
                        ],
                        [
                            'text' => $oTransaction->wdoxidee_ordertransactions__state->value,
                        ],
                        [
                            'text' => $oTransaction->wdoxidee_ordertransactions__amount->value,
                        ],
                        [
                            'text' => $oTransaction->wdoxidee_ordertransactions__currency->value,
                        ],
                        [
                            'text' => $oTransaction->wdoxidee_ordertransactions__date->value,
                            'nowrap' => true,
                        ],
                    ],
                ],
                $this->_getBodyData($oTransaction->getChildTransactions(), $level + 1)
            );
        };

        return $aRowData;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    protected function _getData(): array
    {
        $aTransactions = $this->oOrder->getOrderTransactionList()->getNestedArray();
        return [
            'head' => [
                [
                    'text' => Helper::translate('transactionID'),
                    'nowrap' => true,
                ],
                [
                    'text' => Helper::translate('requestId'),
                    'nowrap' => true,
                ],
                [
                    'text' => Helper::translate('panel_action'),
                    'nowrap' => true,
                ],
                [
                    'text' => Helper::translate('transactionType'),
                    'nowrap' => true,
                ],
                [
                    'text' => Helper::translate('transactionState'),
                    'nowrap' => true,
                ],
                [
                    'text' => Helper::translate('amount'),
                    'nowrap' => true,
                ],
                [
                    'text' => Helper::translate('panel_currency'),
                    'nowrap' => true,
                ],
                [
                    'text' => Helper::translate('panel_transaction_date'),
                    'nowrap' => true,
                ],
            ],
            'body' => $this->_getBodyData($aTransactions),
        ];
    }
}
