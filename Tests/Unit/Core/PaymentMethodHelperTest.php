<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\Oxid\Extend\Model\Basket;
use Wirecard\Oxid\Extend\Model\Payment;

use Wirecard\PaymentSdk\Entity\Mandate;

use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\User;

class PaymentMethodHelperTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    public function testGetPaymentById()
    {
        $oPayment = PaymentMethodHelper::getPaymentById('oxidinvoice');

        $this->assertInstanceOf(Payment::class, $oPayment);
        $this->assertEquals('oxidinvoice', $oPayment->getId());
    }

    public function testGetPayments()
    {
        $this->assertContainsOnlyInstancesOf(Payment::class, PaymentMethodHelper::getPayments());
    }

    public function testGetCurrencyOptions()
    {
        $aCurrencyOptions = PaymentMethodHelper::getCurrencyOptions();

        $this->assertEquals(array_keys($aCurrencyOptions), array_values($aCurrencyOptions));
    }

    public function testGetCountryOptions()
    {
        $aCountryOptions = PaymentMethodHelper::getCountryOptions();

        $this->assertNotEquals(array_keys($aCountryOptions), array_values($aCountryOptions));
    }

    public function testGetModulePayments()
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->load('wdpaypal');
        $oPayment->oxpayments__oxactive = new Field(1);
        $oPayment->save();

        $this->assertCount(1, PaymentMethodHelper::getModulePayments());
    }

    public function testGetMandate()
    {
        $this->assertInstanceOf(Mandate::class, PaymentMethodHelper::getMandate(1));
    }

    public function testGetSepaMandateHtml()
    {
        $oBasketStub = $this->getMockBuilder(Basket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oUserStub = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aDynArray['iban'] = 'DE42512308000000060004';
        Registry::getSession()->setVariable('dynvalue', $aDynArray);

        $sSepaMandate = PaymentMethodHelper::getSepaMandateHtml($oBasketStub, $oUserStub);
        $this->assertContains('DE42512308000000060004', $sSepaMandate);
    }

    public function testPrepareCreditorName()
    {
        $this->assertEquals('John Doe', PaymentMethodHelper::prepareCreditorName());
    }
}
