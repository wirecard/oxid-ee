<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin\Order;

use OxidEsales\Eshop\Application\Model\Order;

use Wirecard\Oxid\Controller\Admin\Tab;
use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\ResponseMapper;
use Wirecard\Oxid\Model\Transaction;

/**
 * Controls the view for a single order tab.
 *
 * @since 1.0.0
 */
class OrderTab extends Tab
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

        $this->oOrder = oxNew(Order::class);
        $this->oTransaction = oxNew(Transaction::class);

        if ($this->_isListObjectIdSet()) {
            $this->oOrder->load($this->sListObjectId);
            $this->oTransaction->loadWithTransactionId($this->oOrder->oxorder__wdoxidee_transactionid->value);

            if ($this->oTransaction->isLoaded()) {
                $this->oResponseMapper = new ResponseMapper($this->oTransaction->getResponseXML());
            }
        }
    }

    /**
     * @inheritdoc
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function render()
    {
        $sTemplate = parent::render();

        if ($this->_isListObjectIdSet()) {
            Helper::addToViewData($this, [
                'emptyText' => Helper::translate('wd_text_order_no_transactions'),
            ]);
        }

        return $sTemplate;
    }
}
