<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

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

    /**
     * @expectedException \OxidEsales\Eshop\Core\Exception\StandardException
     */
    public function testConstructorException()
    {
        $oPaymentMethod = new class extends PaymentMethod{

            public function getTransaction()
            {
                return null;
            }
        };
        $this->assertNull($oPaymentMethod);
    }

    public function testGetOxidPaymentMethodIdfromSdkString()
    {
        $sResult = PaymentMethod::getOxidFromSDKName("paypal");
        $this->assertEquals('wdpaypal', $sResult);
    }

    /**
     * @dataProvider getDefaultConfigFieldsProvider
     */
    public function testGetDefaultConfigFields($sContainsKey)
    {
        $aResult = $this->_oPaymentMethodsStub->getConfigFields();
        $this->assertArrayHasKey($sContainsKey, $aResult);
    }

    public function getDefaultConfigFieldsProvider()
    {
        return [
            "contains apiUrl" => ['apiUrl'],
            "contains httpUser" => ['httpUser'],
            "contains httpPassword" => ['httpPassword'],
            "contains maid" => ['maid'],
            "contains secret" => ['secret'],
            "contains testCredentials" => ['testCredentials'],
        ];
    }

    public function testGetDefaultConfigFieldsCount()
    {
        $aResult = $this->_oPaymentMethodsStub->getConfigFields();
        $this->assertCount(6, $aResult);
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

    public function testGetSupportConfigFields()
    {
        $aSupportConfigFields = $this->_oPaymentMethodsStub->getSupportConfigFields();
        $this->assertCount(2, $aSupportConfigFields);
    }

    public function testGetCheckoutFields()
    {
        $aCheckoutFields = $this->_oPaymentMethodsStub->getCheckoutFields();
        $this->assertEmpty($aCheckoutFields);
    }
}
