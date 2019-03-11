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

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Payment;

/**
 * Controls the view for a single transaction tab.
 */
class TransactionTab extends AdminDetailsController
{
    /**
     * @inheritdoc
     */
    protected $_sThisTemplate = 'transaction_tab.tpl';

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

        $sTransactionId = $this->getEditObjectId();

        if (isset($sTransactionId) && $sTransactionId !== '-1') {
            $this->oTransaction->load($sTransactionId);
            $this->oOrder->load($this->oTransaction->wdoxidee_ordertransactions__wdoxidee_orderid->value);
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
     * @inheritdoc
     *
     * @return string
     */
    public function render(): string
    {
        $this->_aViewData += [
            'listData' => $this->oTransaction->getId() ? $this->getListData() : [],
            'controller' => $this->classKey,
        ];

        return parent::render();
    }

    /**
     * Returns an array of data used to populate the view.
     *
     * @return array
     */
    public function getListData(): array
    {
        return [];
    }

    /**
     * Transforms an associative array to a list data array.
     *
     * @param array $aArray
     * @return array
     */
    protected function _getListDataFromArray(array $aArray): array
    {
        $aListData = [];

        foreach ($aArray as $sKey => $sValue) {
            $aListData[] = [
                'title' => Helper::translate($sKey),
                'value' => $sValue,
            ];
        }

        return $aListData;
    }
}
