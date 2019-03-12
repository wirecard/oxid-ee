<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Model\BaseModel;

/**
 * Class Transaction
 *
 * @package Wirecard\Oxid\Model
 */
class Transaction extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->init('wdoxidee_ordertransactions');
    }

    /**
     * Get the order object for the transaction
     *
     * @return Order
     */
    public function getOrder(): Order
    {
        $sOrderId = $this->wdoxidee_ordertransactions__wdoxidee_orderid->value;
        $oOrder = oxNew(Order::class);
        $oOrder->load($sOrderId);
        return $oOrder;
    }

    public function getPaymentType(): string
    {
        return $this->getOrder()->getPaymentType();
    }
}
