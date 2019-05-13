<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Order;

use Wirecard\Oxid\Extend\Model\PaymentGateway;

class PaymentGatewayTest extends \Wirecard\Test\WdUnitTestCase
{
    /**
     * @var PaymentGateway
     */
    private $_oPaymentGateway;

    protected function setUp()
    {
        $this->_oPaymentGateway = oxNew(PaymentGateway::class);
        parent::setUp();
    }

    /**
     *
     * @dataProvider testCreateTransactionProvider
     */
    public function testCreateTransaction($sPaymentId, $sTransactionClass)
    {
        $oBasketStub = $this->getMockBuilder(Basket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oBasketStub->method('getPaymentId')
            ->willReturn($sPaymentId);

        $oPrice = oxNew(\OxidEsales\Eshop\Core\Price::class);
        $oPrice->setPrice(10.0);

        $oBasketStub->method('getPrice')
            ->willReturn($oPrice);

        $oBasketStub->method('createTransactionBasket')
            ->willReturn(oxNew(\Wirecard\PaymentSdk\Entity\Basket::class));

        $oOrderStub = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oTransaction = $this->_oPaymentGateway->createTransaction($oBasketStub, $oOrderStub);
        $this->assertInstanceOf($sTransactionClass, $oTransaction);
    }

    public function testCreateTransactionProvider()
    {
        return [
            'Credit card transaction' => ['wdcreditcard', \Wirecard\PaymentSdk\Transaction\CreditCardTransaction::class],
            'Paypal transaction' => ['wdpaypal', \Wirecard\PaymentSdk\Transaction\PayPalTransaction::class],
        ];
    }

}
