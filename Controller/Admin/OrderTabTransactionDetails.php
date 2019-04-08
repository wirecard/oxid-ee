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
class OrderTabTransactionDetails extends ListTab
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

        $this->setOrder();
        $this->setTransaction();

        if ($this->_isListObjectIdSet()) {
            $this->oOrder->load($this->sListObjectId);

            if ($this->oTransaction->loadWithTransactionId($this->oOrder->oxorder__wdoxidee_transactionid->value)) {
                $this->oResponseMapper = new ResponseMapper($this->oTransaction->getResponseXML());
            }
        }
    }

    /**
     * Order setter.
     */
    public function setOrder()
    {
        $this->oOrder = oxNew(Order::class);
    }

    /**
     * Transaction setter.
     */
    public function setTransaction()
    {
        $this->oTransaction = oxNew(Transaction::class);
    }

    /**
     * Returns an array of transaction data, sorted in a specific order, used to populate the view.
     *
     * @return array
     */
    protected function _getListData(): array
    {
        if (!$this->oResponseMapper) {
            return array();
        }

        $aTransactionResponseData = $this->oResponseMapper->getData();

        $aSortKeys = [
            'payment-methods.0.name',
            'order-number',
            'request-id',
            'transaction-id',
            'transaction-state',
            'statuses.0.provider-transaction-id'
        ];

        $aRestOfKeys = array_diff(array_keys($aTransactionResponseData), $aSortKeys);
        $aSortedKeys = array_merge($aSortKeys, $aRestOfKeys);

        $aList = array();
        foreach ($aSortedKeys as $sKey) {
            $aList[] = [
                'title' => $sKey,
                'value' => $aTransactionResponseData[$sKey] ?? null
            ];
        }

        return $aList;
    }
}
