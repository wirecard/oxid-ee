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
 * Controls the view for the data transaction tab.
 *
 * @since 1.0.0
 */
class TransactionTabTransactionDetails extends TransactionTab
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
        /**
         * Possible translation keys for the PhraseApp parsing script to pick up:
         *
         * translate('wd_maid')
         * translate('wd_transactionID')
         * translate('wd_requestId')
         * translate('wd_transactionType')
         * translate('wd_transactionState')
         * translate('wd_requestedAmount')
         * translate('wd_descriptor')
         */
        return $this->_getListDataFromArray(
            $this->_oResponseMapper->getTransactionDetails(),
            $this->_oTransaction->wdoxidee_ordertransactions__state->value
        );
    }
}
