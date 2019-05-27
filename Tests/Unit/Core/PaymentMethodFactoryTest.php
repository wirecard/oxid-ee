<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Core\PaymentMethodFactory;
use Wirecard\Oxid\Model\CreditCardPaymentMethod;
use Wirecard\Oxid\Model\PayolutionInvoicePaymentMethod;
use Wirecard\Oxid\Model\PaypalPaymentMethod;
use Wirecard\Oxid\Model\RatepayInvoicePaymentMethod;
use Wirecard\Oxid\Model\SepaCreditTransferPaymentMethod;
use Wirecard\Oxid\Model\SepaDirectDebitPaymentMethod;

use Wirecard\Oxid\Model\PaymentMethod;

class PaymentMethodFactoryTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    public function testGetPaymentMethodClasses()
    {
        foreach (PaymentMethodFactory::getPaymentMethodClasses() as $aClass) {
            $this->assertInstanceOf(PaymentMethod::class, new $aClass());
        }
    }

    /**
     * @expectedException \OxidEsales\Eshop\Core\Exception\StandardException
     */
    public function testInvalidPaymentMethod()
    {
        PaymentMethodFactory::create('invalid');
    }

    public function testCreatePaypal()
    {
        $oPaymentMethod = PaymentMethodFactory::create("wdpaypal");
        $this->assertInstanceOf(PaypalPaymentMethod::class, $oPaymentMethod);
    }

    public function testCreateCreditCard()
    {
        $oPaymentMethod = PaymentMethodFactory::create("wdcreditcard");
        $this->assertInstanceOf(CreditCardPaymentMethod::class, $oPaymentMethod);
    }

    public function testCreateSepaCreditTransfer()
    {
        $oPaymentMethod = PaymentMethodFactory::create("wdsepacredit");
        $this->assertInstanceOf(SepaCreditTransferPaymentMethod::class, $oPaymentMethod);
    }

    public function testCreateSepaDirectDebit()
    {
        $oPaymentMethod = PaymentMethodFactory::create("wdsepadd");
        $this->assertInstanceOf(SepaDirectDebitPaymentMethod::class, $oPaymentMethod);
    }

    public function testCreatePayolutionInvoice()
    {
        $oPaymentMethod = PaymentMethodFactory::create("wdpayolution-inv");
        $this->assertInstanceOf(PayolutionInvoicePaymentMethod::class, $oPaymentMethod);
    }

    public function testCreateRatepayInvoice()
    {
        $oPaymentMethod = PaymentMethodFactory::create("wdratepay-invoice");
        $this->assertInstanceOf(RatepayInvoicePaymentMethod::class, $oPaymentMethod);
    }
}
