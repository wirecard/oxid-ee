<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\TransactionList;
use Wirecard\Oxid\Extend\Model\Order as WdOrder;
use Wirecard\Oxid\Extend\Model\Payment;

use Wirecard\PaymentSdk\Entity\AccountHolder;

use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;

class OrderTest extends Wirecard\Test\WdUnitTestCase
{
    protected function dbData()
    {
        return [
            [
                'table' => 'oxorder',
                'columns' => [
                    'oxid',
                    'oxpaymenttype',
                    'wdoxidee_orderstate',
                    'wdoxidee_transactionid',
                ],
                'rows' => [
                    ['1', 'wdpaypal', WdOrder::STATE_AUTHORIZED, '1'],
                    ['2', 'oxidinvoice', WdOrder::STATE_PROCESSING, '2'],
                    ['3', 'wdcreditcard', WdOrder::STATE_CANCELED, '3'],
                ],
            ],
            [
                'table' => 'wdoxidee_ordertransactions',
                'columns' => [
                    'oxid',
                    'transactionid',
                    'type',
                ],
                'rows' => [
                    ['1', '1', 'pending'],
                    ['2', '2', null],
                ],
            ],
            [
               'table' => 'oxpayments',
               'columns' => [
                   'oxid',
                   'wdoxidee_isours',
                   'wdoxidee_delete_canceled_order',
                   'wdoxidee_delete_failed_order',
               ],
               'rows' => [
                   ['oxidinvoice', false, false, false],
                   ['wdpaypal', true, true, false],
                   ['wdcreditcard', true, true, true],
               ],
           ]
        ];
    }

    public function testLoadWithTransactionId()
    {
        $oOrder = oxNew(Order::class);

        $this->assertTrue($oOrder->loadWithTransactionId('1'));
    }

    public function testGetOrderBillingCountry()
    {
        $oOrder = oxNew(Order::class);

        $this->assertInstanceOf(Country::class, $oOrder->getOrderBillingCountry());
    }

    public function testGetOrderShippingCountry()
    {
        $oOrder = oxNew(Order::class);

        $this->assertInstanceOf(Country::class, $oOrder->getOrderShippingCountry());
    }

    public function testGetOrderPayment()
    {
        $oOrder = oxNew(Order::class);

        $this->assertInstanceOf(Payment::class, $oOrder->getOrderPayment());
    }

    public function testGetOrderTransactionList()
    {
        $oOrder = oxNew(Order::class);

        $this->assertInstanceOf(TransactionList::class, $oOrder->getOrderTransactionList());
    }

    /**
     * @dataProvider testIsCustomPaymentMethodProvider
     */
    public function testIsCustomPaymentMethod($orderId, $isCustomPaymentMethod)
    {
        $oOrder = oxNew(Order::class);
        $oOrder->load($orderId);

        $this->assertEquals($oOrder->isCustomPaymentMethod(), $isCustomPaymentMethod);
    }

    public function testIsCustomPaymentMethodProvider()
    {
        return [
            'order with custom payment method' => ['1', true],
            'order with foreign payment method' => ['2', false],
        ];
    }

    /**
     * @dataProvider testIsPaymentPendingProvider
     */
    public function testIsPaymentPending($orderId, $isPaymentPending)
    {
        $oOrder = oxNew(Order::class);
        $oOrder->load($orderId);

        $this->assertEquals($oOrder->isPaymentPending(), $isPaymentPending);
    }

    public function testIsPaymentPendingProvider()
    {
        return [
            'order with pending transaction' => ['1', true],
            'order with non-pending transaction' => ['2', false],
        ];
    }

    /**
     * @dataProvider testIsPaymentSuccessProvider
     */
    public function testIsPaymentSuccess($orderId, $isPaymentSuccess)
    {
        $oOrder = oxNew(Order::class);
        $oOrder->load($orderId);

        $this->assertEquals($oOrder->isPaymentSuccess(), $isPaymentSuccess);
    }

    public function testIsPaymentSuccessProvider()
    {
        return [
            'authorized order' => ['1', true],
            'processing order' => ['2', true],
            'canceled order' => ['3', false],
        ];
    }

    public function testGetAccountHolder()
    {
        $oOrder = oxNew(Order::class);

        $this->assertInstanceOf(AccountHolder::class, $oOrder->getAccountHolder());
    }

    public function testGetShippingAccountHolder()
    {
        $oOrder = oxNew(Order::class);

        $this->assertInstanceOf(AccountHolder::class, $oOrder->getShippingAccountHolder());
    }

    /**
     * @dataProvider testHandleOrderStateProvider
     */
    public function testHandleOrderState($orderId, $state, $shouldBeDeleted)
    {
        $oOrder = oxNew(Order::class);
        $oOrder->load($orderId);
        $oOrder->handleOrderState($state);
        $this->assertEquals(!$oOrder->load($orderId), $shouldBeDeleted);
    }
    public function testHandleOrderStateProvider()
    {
        return [
            'order with foreign payment method: canceled' => ['2', WdOrder::STATE_CANCELED, false],
            'order with foreign payment method: failed' => ['2', WdOrder::STATE_FAILED, false],
            'order to delete on canceled but not on failed: canceled' => ['1', WdOrder::STATE_CANCELED, true],
            'order to delete on canceled but not on failed: failed' => ['1', WdOrder::STATE_FAILED, false],
            'order to delete on both canceled and failed: canceled' => ['3', WdOrder::STATE_CANCELED, true],
            'order to delete on both canceled and failed: failed' => ['3', WdOrder::STATE_FAILED, true],
        ];
    }
}
