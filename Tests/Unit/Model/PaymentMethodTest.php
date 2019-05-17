<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Application\Model\Payment;

use Wirecard\Oxid\Model\PaymentMethod;

class PaymentMethodTest extends OxidEsales\TestingLibrary\UnitTestCase
{

    /**
     * @var PaymentMethod
     */
    private $_oPaymentMethodsStub;

    protected function setUp()
    {
        $this->_oPaymentMethodsStub = $this->getMockForAbstractClass(
            PaymentMethod::class,
            [],
            '',
            false
        );
        parent::setUp();
    }

    public function testGetOxidPaymentMethodIdfromSdkString()
    {
        $sResult = PaymentMethod::getOxidFromSDKName("paypal");
        $this->assertEquals('wdpaypal', $sResult);
    }

    public function testGetDefaultConfigFields()
    {
        $aResult = $this->_oPaymentMethodsStub->getConfigFields();
        $this->assertCount(6, $aResult);
        $this->assertArrayHasKey('apiUrl', $aResult);
        $this->assertArrayHasKey('httpUser', $aResult);
        $this->assertArrayHasKey('httpPassword', $aResult);
        $this->assertArrayHasKey('maid', $aResult);
        $this->assertArrayHasKey('secret', $aResult);
        $this->assertArrayHasKey('testCredentials', $aResult);
    }

    public function testGetLogoPath()
    {
        $sLogoUrl = $this->_oPaymentMethodsStub->getLogoPath();
        $this->assertContains("wirecard/paymentgateway/out/img/", $sLogoUrl);
    }

    public function getPublicFieldNames()
    {
        $aPublicFieldNames = $this->_oPaymentMethodsStub->getPublicFieldNames();
        $this->assertCount(2, $aPublicFieldNames);
        $this->assertContains('apiUrl', $aPublicFieldNames);
        $this->assertContains('maid', $aPublicFieldNames);
    }

    public function getSupportConfigFields()
    {
        $aSupportConfigFields = $this->_oPaymentMethodsStub->getSupportConfigFields();
        $this->assertCount(2, $aSupportConfigFields);
    }
}
