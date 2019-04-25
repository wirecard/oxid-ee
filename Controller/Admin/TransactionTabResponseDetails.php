<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin;

use \OxidEsales\Eshop\Application\Model\Order;

use \Wirecard\Oxid\Core\ResponseMapper;
use \Wirecard\Oxid\Model\Transaction;

/**
 * Controls the view for the order details tab.
 */
class TransactionTabResponseDetails extends ListTab
{
    /**
     * @var Transaction
     */
    protected $oTransaction;

    /**
     * @var Order
     */
    protected $oOrder;

    /**
     * @var ResponseMapper
     */
    protected $oResponseMapper;

    /**
     * OrderTabDetails constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTransaction();

        if ($this->_isListObjectIdSet() && $this->oTransaction->load($this->getEditObjectId())) {
            $this->oResponseMapper = new ResponseMapper($this->oTransaction->getResponseXML());
        }
    }

    /**
     * Transaction setter.
     */
    public function setTransaction()
    {
        $this->oTransaction = oxNew(Transaction::class);
    }

    /**
     * Returns an array of transaction response details data.
     * Some properties of the response are moved to the beginning of the array in this method
     * in order to make the most important information easily graspable at the top.
     * This array is used to populate the view.
     *
     * @return array
     */
    protected function _getListData(): array
    {
        if (!$this->oResponseMapper) {
            return array();
        }

        $aTransactionRespData = $this->oResponseMapper->getData();

        $aSortKeys = [
            'payment-methods.0.name',
            'order-number',
            'request-id',
            'transaction-id',
            'transaction-state',
            'statuses.0.provider-transaction-id'
        ];

        $aRestOfKeys = array_diff(array_keys($aTransactionRespData), $aSortKeys);
        $aSortedKeys = array_merge($aSortKeys, $aRestOfKeys);

        $aList = array();
        foreach ($aSortedKeys as $sKey) {
            $aList[] = [
                'title' => $sKey,
                'value' => $aTransactionRespData[$sKey] ?? null
            ];
        }

        return $aList;
    }
}
