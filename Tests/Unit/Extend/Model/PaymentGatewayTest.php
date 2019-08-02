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
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\Transaction\SepaDirectDebitTransaction;
use Wirecard\PaymentSdk\Transaction\SofortTransaction;

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
     * @dataProvider createTransactionProvider
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
        $oOrderStub->method('getAccountHolder')
            ->willReturn(new AccountHolder());

        $oTransaction = $this->_oPaymentGateway->createTransaction($oBasketStub, $oOrderStub);
        $this->assertInstanceOf($sTransactionClass, $oTransaction);
    }

    public function createTransactionProvider()
    {
        return [
            'Credit card transaction' => ['wdcreditcard', CreditCardTransaction::class],
            'PayPal transaction' => ['wdpaypal', PayPalTransaction::class],
            'RatePay transaction' => ['wdratepay-invoice', RatepayInvoiceTransaction::class],
            'SEPA Direct Debit transaction' => ['wdsepadd', SepaDirectDebitTransaction::class],
            'Sofort. transaction' => ['wdsofortbanking', SofortTransaction::class],
        ];
    }

    public function testSetAndRetrieveModuleToken()
    {
        $sToken = PaymentGateway::setAndRetrieveModuleToken($this->getSession());

        $this->assertEquals('wdpayment=' . $this->getSession()->getVariable('wdtoken'), $sToken);
    }
}
