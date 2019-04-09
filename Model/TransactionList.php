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
     * Returns a list of child transactions for the transaction with the given ID.
     *
     * @param string $sOxid
     * @return TransactionList
     */
    public function getChildList($sOxid)
    {
        if (!$sOxid) {
            return $this;
        }

        $oDb = DatabaseProvider::getDb();
        $oListObject = $this->getBaseObject();
        $sFieldList = $oListObject->getSelectFields();
        $sQuery = "select {$sFieldList} from {$oListObject->getViewName()}
            where {$oListObject->getViewName()}.parenttransactionid = {$oDb->quote($sOxid)}";

        $this->selectString($sQuery);

        return $this;
    }
}
