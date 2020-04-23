<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Extend\Model\Payment;
use Wirecard\Oxid\Model\PaymentMethod\CreditCardPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\PaypalPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\SofortPaymentMethod;

class PaymentTest extends OxidEsales\TestingLibrary\UnitTestCase
{

    /**
     * @dataProvider isCustomPaymentMethodProvider
     */
    public function testIsCustomPaymentMethod($fieldValue, $expected)
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->oxpayments__wdoxidee_isours = new oxField($fieldValue);

        $this->assertEquals($oPayment->isCustomPaymentMethod(), $expected);
    }

    public function isCustomPaymentMethodProvider()
    {
        return [
            'true field' => [true, true],
            'false field' => [false, false],
            'truthy field' => [1, true],
            'falsy field' => [null, false],
        ];
    }

    /**
     * @dataProvider getPaymentMethodProvider
     */
    public function testGetPaymentMethod($paymentMethodType, $expected)
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->setId($paymentMethodType);

        if ($expected) {
            $this->assertInstanceOf($expected, $oPayment->getPaymentMethod());
        } else {
            $this->assertNull($expected, $oPayment->getPaymentMethod());
        }
    }

    public function getPaymentMethodProvider()
    {
        return [
            'valid Paypal payment method' => ['wdpaypal', PaypalPaymentMethod::class],
            'valid Credit Card payment method' => ['wdcreditcard', CreditCardPaymentMethod::class],
            'valid Sofort. payment method' => ['wdsofortbanking', SofortPaymentMethod::class],
            'invalid payment method' => ['foo', null],
        ];
    }

    /**
     * @dataProvider getLogoUrlProvider
     */
    public function testGetLogoUrl($paymentMethodType, $sExpected)
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->load($paymentMethodType);

        $logoUrl = $oPayment->getLogoUrl();

        if ($sExpected) {
            $this->assertContains($sExpected, $logoUrl);
        } else {
            $this->assertNull($logoUrl);
        }
    }

    public function getLogoUrlProvider()
    {
        return [
            'Paypal logo url' => ['wdpaypal', 'paypal.png'],
            'Credit Card logo url' => ['wdcreditcard', 'creditcard.png'],
            'Sofort. logo url' => ['wdsofortbanking', 'klarna.com'],
            'invalid payment method' => ['invalid', null],
        ];
    }

    /**
     * @dataProvider shouldRenderCreditCardFormProvider
     */
    public function testShouldRenderCreditCardFormProvider($sPaymentMethodName, $sToken, $bExpected)
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->load($sPaymentMethodName);

        $aDynArray[CreditCardPaymentMethod::CARD_TOKEN_FIELD] = $sToken;
        Registry::getSession()->setVariable('dynvalue', $aDynArray);

        $this->assertEquals($bExpected, $oPayment->shouldRenderCreditCardForm());
    }

    public function shouldRenderCreditCardFormProvider()
    {
        return [
            'Credit Card with token set' => ['wdcreditcard', 'token 1', false],
            'Credit Card no token set' => ['wdcreditcard', CreditCardPaymentMethod::NEW_CARD_TOKEN, true],
            'No credit card' => ['wdsepadd', CreditCardPaymentMethod::NEW_CARD_TOKEN, false],
        ];
    }
}
