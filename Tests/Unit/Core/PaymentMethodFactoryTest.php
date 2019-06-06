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
use Wirecard\Oxid\Model\PaymentMethod;
use Wirecard\Oxid\Model\PayolutionInvoicePaymentMethod;
use Wirecard\Oxid\Model\PaypalPaymentMethod;
use Wirecard\Oxid\Model\RatepayInvoicePaymentMethod;
use Wirecard\Oxid\Model\SepaCreditTransferPaymentMethod;
use Wirecard\Oxid\Model\SepaDirectDebitPaymentMethod;
use Wirecard\Oxid\Model\SofortPaymentMethod;

class PaymentMethodFactoryTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    public function testGetPaymentMethodClasses()
    {
        foreach (PaymentMethodFactory::getPaymentMethodClasses() as $aClass) {
            $this->assertInstanceOf(PaymentMethod::class, new $aClass());
        }
    }

    /**
     * @dataProvider createProvider
     */
    public function testCreate($sPaymentMethodType, $oExpectedClassName)
    {
        $this->assertInstanceOf($oExpectedClassName, PaymentMethodFactory::create($sPaymentMethodType));
    }

    public function createProvider()
    {
        return [
            'PayPal payment method' => ['wdpaypal', PaypalPaymentMethod::class],
            'Credit Card payment method' => ['wdcreditcard', CreditCardPaymentMethod::class],
            'SEPA CT payment method' => ['wdsepacredit', SepaCreditTransferPaymentMethod::class],
            'SEPA DD payment method' => ['wdsepadd', SepaDirectDebitPaymentMethod::class],
            'Sofort. payment method' => ['wdsofortbanking', SofortPaymentMethod::class],
            'payolution payment method' => ['wdpayolution-inv', PayolutionInvoicePaymentMethod::class],
            'Ratepay payment method' => ['wdratepay-invoice', RatepayInvoicePaymentMethod::class],
        ];
    }

    /**
     * @expectedException \OxidEsales\Eshop\Core\Exception\StandardException
     */
    public function testInvalidPaymentMethod()
    {
        PaymentMethodFactory::create('invalid');
    }
}
