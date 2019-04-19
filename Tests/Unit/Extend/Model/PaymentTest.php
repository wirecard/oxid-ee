<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Extend\Model\Payment;
use Wirecard\Oxid\Model\PaypalPaymentMethod;

class PaymentTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @dataProvider testIsCustomPaymentMethodProvider
     */
    public function testIsCustomPaymentMethod($fieldValue, $expected)
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->oxpayments__wdoxidee_isours = new oxField($fieldValue);

        $this->assertEquals($oPayment->isCustomPaymentMethod(), $expected);
    }

    public function testIsCustomPaymentMethodProvider()
    {
        return [
            'true field' => [true, true],
            'false field' => [false, false],
            'truthy field' => [1, true],
            'falsy field' => [null, false],
        ];
    }

    /**
     * @dataProvider testGetPaymentMethodProvider
     */
    public function testGetPaymentMethod($paymentMethodType, $expected)
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->setId('wdpaypal');

        if ($expected) {
            $this->assertInstanceOf($expected, $oPayment->getPaymentMethod());
        } else {
            $this->assertNull($expected, $oPayment->getPaymentMethod());
        }
    }

    public function testGetPaymentMethodProvider()
    {
        return [
            'valid payment method' => ['wdpaypal', PaypalPaymentMethod::class],
            'invalid payment method' => ['foo', null],
        ];
    }
}
