<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Model\ListModel;

/**
 * List Model for Transaction Lists.
 *
 * @since 1.0.0
 */
class TransactionList extends ListModel
{
    /**
     * @inheritdoc
     *
     * @since 1.0.0
     */
    protected $_sObjectsInListName = Transaction::class;

    /**
     * Returns a list of transactions based on an associative array where each key has to match the value.
     *
     * @param array  $aConditions
     * @param string $sOrderByField
     *
     * @return TransactionList
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     *
     * @since 1.0.0
     */
    public function getListByConditions($aConditions = [], $sOrderByField = 'date')
    {
        $oDb = DatabaseProvider::getDb();
        $oListObject = $this->getBaseObject();
        $sViewName = $oListObject->getViewName();
        $sQuery = "SELECT {$oListObject->getSelectFields()} FROM {$sViewName} WHERE 1";

        foreach ($aConditions as $sConditionKey => $sConditionValue) {
            $sQuery .= " AND {$sViewName}.{$sConditionKey} = {$oDb->quote($sConditionValue)}";
        }

        $sQuery .= " ORDER BY {$sViewName}.{$sOrderByField}";

        $this->selectString($sQuery);

        return $this;
    }

    /**
     * Returns an array of child transaction IDs of a given transaction.
     *
     * @param Transaction $oTransaction
     *
     * @return array
     *
     * @since 1.0.0
     */
    protected function _getChildTransactionIds($oTransaction)
    {
        $aChildTransactionIds = [];

        foreach ($oTransaction->getChildTransactions() as $sTransactionId => $oChildTransaction) {
            $aChildTransactionIds = array_merge(
                $aChildTransactionIds,
                [$sTransactionId],
                $this->_getChildTransactionIds($oChildTransaction)
            );
        }

        return $aChildTransactionIds;
    }

    /**
     * Returns an array of transactions while omitting all items that are already referenced in a childTransactions
     * property.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getNestedArray()
    {
        $aNestedArray = $this->_aArray;

        // the reference is used deliberately here so that foreach re-calculates the array length
        foreach ($aNestedArray as $sTransactionId => &$oTransaction) {
            foreach ($this->_getChildTransactionIds($oTransaction) as $sTransactionId) {
                unset($aNestedArray[$sTransactionId]);
            }
        }

        return $aNestedArray;
    }
}
