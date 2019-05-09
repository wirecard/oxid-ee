<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Order;

use Wirecard\Oxid\Extend\Model\Order as WdOrder;
use Wirecard\Oxid\Extend\Model\Payment;
use Wirecard\Oxid\Model\TransactionList;

use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Entity\AccountHolder;

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
                    ['1', 'wdpaypal', BackendService::TYPE_AUTHORIZED, '1'],
                    ['2', 'oxidinvoice', BackendService::TYPE_PROCESSING, '2'],
                    ['3', 'wdcreditcard', BackendService::TYPE_CANCELLED, '1'],
                    ['4', 'wdcreditcard', BackendService::TYPE_REFUNDED, '2'],
                    ['5', 'wdpaypal', BackendService::TYPE_PROCESSING, '3'],
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
                    ['3', '3', 'failed'],
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
            ],
            [
                'table' => 'oxorderarticles',
                'columns' => [
                    'oxid',
                    'oxorderid',
                    'oxartid',
                ],
                'rows' => [
                    ['oxid1', '1', 'article id 1'],
                    ['oxid2', '1', 'article id 2'],
                ]
            ],
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

    /**
     * @dataProvider testIsPaymentRefundedProvider
     */
    public function testIsPaymentRefunded($sOrderId, $bIsPaymentRefunded)
    {
        $oOrder = oxNew(Order::class);
        $oOrder->load($sOrderId);

        $this->assertEquals($oOrder->isPaymentRefunded(), $bIsPaymentRefunded);
    }

    public function testIsPaymentRefundedProvider()
    {
        return [
            'order with authorized transaction' => ['1', false],
            'order with processing transaction' => ['2', false],
            'order with cancelled transaction' => ['3', false],
            'order with refunded transaction' => ['4', true],
            'order with failed transaction' => ['5', false],
        ];
    }

    /**
     * @dataProvider testIsPaymentCancelledProvider
     */
    public function testIsPaymentCancelled($sOrderId, $bIsPaymentCancelled)
    {
        $oOrder = oxNew(Order::class);
        $oOrder->load($sOrderId);

        $this->assertEquals($oOrder->isPaymentCancelled(), $bIsPaymentCancelled);
    }

    public function testIsPaymentCancelledProvider()
    {
        return [
            'order with authorized transaction' => ['1', false],
            'order with processing transaction' => ['2', false],
            'order with cancelled transaction' => ['3', true],
            'order with refunded transaction' => ['4', false],
            'order with failed transaction' => ['5', false],
        ];
    }

    /**
     * @dataProvider testIsPaymentFailedProvider
     */
    public function testIsPaymentFailed($sOrderId, $bIsPaymentFailed)
    {
        $oOrder = oxNew(Order::class);
        $oOrder->load($sOrderId);

        $this->assertEquals($oOrder->isPaymentFailed(), $bIsPaymentFailed);
    }

    public function testIsPaymentFailedProvider()
    {
        return [
            'order with authorized transaction' => ['1', false],
            'order with processing transaction' => ['2', false],
            'order with cancelled transaction' => ['3', false],
            'order with refunded transaction' => ['4', false],
            'order with failed transaction' => ['5', true],
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
            'order with foreign payment method: canceled' => ['2', WdOrder::STATE_CANCELLED, false],
            'order with foreign payment method: failed' => ['2', WdOrder::STATE_FAILED, false],
            'order to delete on canceled but not on failed: canceled' => ['1', WdOrder::STATE_CANCELLED, true],
            'order to delete on canceled but not on failed: failed' => ['1', WdOrder::STATE_FAILED, false],
            'order to delete on both canceled and failed: canceled' => ['3', WdOrder::STATE_CANCELLED, true],
            'order to delete on both canceled and failed: failed' => ['3', WdOrder::STATE_FAILED, true],
        ];
    }

    /**
     * @dataProvider testIsLastArticleProvider
     */
    public function testIsLastArticle($sArticleId, $bExpected)
    {
        $oOrder = oxNew(Order::class);
        $oOrder->load('1');

        $oOrderArticle = oxNew(\OxidEsales\Eshop\Application\Model\OrderArticle::class);
        $oOrderArticle->load($sArticleId);

        $isLastArticle = $oOrder->isLastArticle($oOrderArticle);
        $this->assertEquals($bExpected, $isLastArticle);
    }

    public function testIsLastArticleProvider()
    {
        return [
            'correct last article' => ['oxid2', true],
            'not last article' => ['oxid1', false],
        ];
    }
}
