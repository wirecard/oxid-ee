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
use Wirecard\Oxid\Core\ResponseMapper;
use Wirecard\Oxid\Model\Transaction;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Payment;

/**
 * Controls the view for a single transaction tab.
 */
class TransactionTab extends ListTab
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
     * @var Payment
     */
    protected $oPayment;

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

        $this->setTransaction();
        $this->setOrder();
        $this->setPayment();

        if ($this->_isListObjectIdSet()) {
            $this->oTransaction->load($this->sListObjectId);
            $this->oOrder->load($this->oTransaction->wdoxidee_ordertransactions__orderid->value);
            $this->oPayment->load($this->oOrder->oxorder__oxpaymenttype->value);

            $this->setResponseMapper();
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
     * Payment setter.
     */
    public function setPayment()
    {
        $this->oPayment = oxNew(Payment::class);
    }

    /**
     * ResponseMapper setter.
     */
    public function setResponseMapper()
    {
        $this->oResponseMapper = new ResponseMapper($this->oTransaction->getResponseXML());
    }

    /**
     * Transforms an associative array to a list data array.
     *
     * @param array  $aArray
     * @param string $sTransactionState
     * @return array
     */
    protected function _getListDataFromArray(array $aArray, string $sTransactionState = null): array
    {
        $aListData = [];

        foreach ($aArray as $sKey => $sValue) {
            // add current transaction state as a hint if it differs from the response transaction state
            if ($sTransactionState && $sKey === 'transactionState') {
                $sValue = $sTransactionState !== Transaction::STATE_AWAITING
                    ? $sValue
                    : $sValue . ' (confirmation awaiting)';
            }

            $aListData[] = [
                'title' => Helper::translate($sKey),
                'value' => $sValue,
            ];
        }

        return $aListData;
    }
}
