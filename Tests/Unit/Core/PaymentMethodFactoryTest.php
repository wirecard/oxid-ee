<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Core\PaymentMethodFactory;

class PaymentMethodFactoryTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    public function testCreatePaypal()
    {
        $oPaymentMethod = PaymentMethodFactory::create("wdpaypal");
        $this->assertInstanceOf(\Wirecard\Oxid\Model\PaypalPaymentMethod::class, $oPaymentMethod);
    }

    public function testCreateCreditCard()
    {
        $oPaymentMethod = PaymentMethodFactory::create("wdcreditcard");
        $this->assertInstanceOf(\Wirecard\Oxid\Model\CreditCardPaymentMethod::class, $oPaymentMethod);
    }
}
