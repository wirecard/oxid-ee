<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin\Transaction;

use Wirecard\Oxid\Controller\Admin\Tab;
use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\ResponseMapper;
use Wirecard\Oxid\Model\Transaction;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Payment;

/**
 * Controls the view for a single transaction tab.
 *
 * @since 1.0.0
 */
class TransactionTab extends Tab
{
    /**
     * @var Transaction
     *
     * @since 1.0.0
     */
    protected $oTransaction;

    /**
     * @var Order
     *
     * @since 1.0.0
     */
    protected $oOrder;

    /**
     * @var Payment
     *
     * @since 1.0.0
     */
    protected $oPayment;

    /**
     * @var ResponseMapper
     *
     * @since 1.0.0
     */
    protected $oResponseMapper;

    /**
     * TransactionTab constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->oTransaction = oxNew(Transaction::class);
        $this->oOrder = oxNew(Order::class);
        $this->oPayment = oxNew(Payment::class);

        if ($this->_isListObjectIdSet()) {
            $this->oTransaction->load($this->sListObjectId);
            $this->oOrder->load($this->oTransaction->wdoxidee_ordertransactions__orderid->value);
            $this->oPayment->load($this->oOrder->oxorder__oxpaymenttype->value);

            $this->oResponseMapper = new ResponseMapper($this->oTransaction->getResponseXML());
        }
    }

    /**
     * Transforms an associative array to a list data array.
     *
     * @param array  $aArray
     * @param string $sTransactionState
     * @return array
     *
     * @since 1.0.0
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