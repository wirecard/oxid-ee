<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use Wirecard\Oxid\Model\Transaction;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Model\ListModel;

/**
 * List Model for Transaction Lists.
 */
class TransactionList extends ListModel
{
    /**
     * @inheritdoc
     */
    protected $_sObjectsInListName = Transaction::class;

    /**
     * Returns a list of transactions based on an associative array where each key has to match the value.
     *
     * @param array  $aConditions
     * @param string $sOrderByField
     * @return TransactionList
     */
    public function getListByConditions(array $aConditions = [], string $sOrderByField = 'date'): TransactionList
    {
        $oDb = DatabaseProvider::getDb();
        $oListObject = $this->getBaseObject();
        $sViewName = $oListObject->getViewName();
        $sQuery = "select {$oListObject->getSelectFields()} from {$sViewName} where 1";

        foreach ($aConditions as $sConditionKey => $sConditionValue) {
            $sQuery .= " and {$sViewName}.{$sConditionKey} = {$oDb->quote($sConditionValue)}";
        }

        $sQuery .= " order by {$sViewName}.{$sOrderByField}";

        $this->selectString($sQuery);

        return $this;
    }
}
