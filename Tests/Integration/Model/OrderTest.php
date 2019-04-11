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
    public function testIsCustomPaymentMethod($input, $expected)
    {
        $oOrder = oxNew(Order::class);
        $oOrder->load($input);

        $this->assertEquals($oOrder->isCustomPaymentMethod(), $expected);
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
    public function testIsPaymentPending($input, $expected)
    {
        $oOrder = oxNew(Order::class);
        $oOrder->load($input);

        $this->assertEquals($oOrder->isPaymentPending(), $expected);
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
    public function testIsPaymentSuccess($input, $expected)
    {
        $oOrder = oxNew(Order::class);
        $oOrder->load($input);

        $this->assertEquals($oOrder->isPaymentSuccess(), $expected);
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
}
