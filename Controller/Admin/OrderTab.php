<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin;

use Wirecard\Oxid\Core\ResponseMapper;
use Wirecard\Oxid\Model\Transaction;

use OxidEsales\Eshop\Application\Model\Order;

/**
 * Controls the view for a single order tab.
 */
class OrderTab extends Tab
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
     * TransactionTab constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setOrder();
        $this->setTransaction();

        if ($this->_isListObjectIdSet()) {
            $this->oOrder->load($this->sListObjectId);
            $this->oTransaction->loadWithTransactionId($this->oOrder->oxorder__wdoxidee_transactionid->value);

            if ($this->oTransaction->isLoaded()) {
                $this->setResponseMapper();
            }
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
     * Order setter.
     */
    public function setOrder()
    {
        $this->oOrder = oxNew(Order::class);
    }

    /**
     * ResponseMapper setter.
     */
    public function setResponseMapper()
    {
        $this->oResponseMapper = new ResponseMapper($this->oTransaction->getResponseXML());
    }
}
