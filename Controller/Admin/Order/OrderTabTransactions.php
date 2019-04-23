<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin\Order;

use Wirecard\Oxid\Core\Helper;

/**
 * Controls the view for the order transaction tab.
 *
 * @since 1.0.0
 */
class OrderTabTransactions extends OrderTab
{
    /**
     * @inheritdoc
     *
     * @since 1.0.0
     */
    protected $_sThisTemplate = 'tab_table.tpl';

    /**
     * Returns an array to populate the table body.
     *
     * @param array $aTransactions
     * @param int   $level
     * @return array
     *
     * @since 1.0.0
     */
    protected function _getBodyData($aTransactions, $level = 0)
    {
        $aRowData = [];

        foreach ($aTransactions as $oTransaction) {
            $sTransSecurity = Helper::translate('wd_secured');
            if (!$oTransaction->wdoxidee_ordertransactions__validsignature->value) {
                $sTransSecurity = Helper::translate('wd_manipulated');
            }

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
                        // [
                        //     'text' => $sTransSecurity,
                        // ],
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
     *
     * @since 1.0.0
     */
    protected function _getData()
    {
        $aTransactions = $this->oOrder->getOrderTransactionList()->getNestedArray();
        $aBodyData = $this->_getBodyData($aTransactions);

        return $aBodyData ? [
            'head' => [
                [
                    'text' => Helper::translate('wd_transactionID'),
                    'nowrap' => true,
                ],
                [
                    'text' => Helper::translate('wd_requestId'),
                    'nowrap' => true,
                ],
                [
                    'text' => Helper::translate('wd_panel_action'),
                    'nowrap' => true,
                ],
                [
                    'text' => Helper::translate('wd_transactionType'),
                    'nowrap' => true,
                ],
                [
                    'text' => Helper::translate('wd_transactionState'),
                    'nowrap' => true,
                ],
                [
                    'text' => Helper::translate('wd_amount'),
                    'nowrap' => true,
                ],
                [
                    'text' => Helper::translate('wd_panel_currency'),
                    'nowrap' => true,
                ],
                [
                    'text' => Helper::translate('wd_panel_transaction_date'),
                    'nowrap' => true,
                ],
                // [
                //     'text' => Helper::translate('wd_secured'),
                //     'nowrap' => true,
                // ],
            ],
            'body' => $aBodyData,
        ] : [];
    }
}
