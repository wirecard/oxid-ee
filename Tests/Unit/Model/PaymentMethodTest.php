<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\PaymentMethod\PaymentMethod;

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
        $oPaymentMethod = new class extends PaymentMethod
        {

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

    public function testDefaultCheckoutFields()
    {
        $this->assertEquals([], $this->_oPaymentMethodsStub->getCheckoutFields());
    }

    public function testDefaultMetaDataFieldNames()
    {
        $this->assertEquals(['initial_title'], $this->_oPaymentMethodsStub->getMetaDataFieldNames());
    }

    public function testDefaultSupportConfigFields()
    {
        $aFieldKeys = array_keys($this->_oPaymentMethodsStub->getSupportConfigFields());

        $this->assertEquals([
            'apiUrl',
            'maid',
        ], $aFieldKeys);
    }

    public function testGetLogoPath()
    {
        $sLogoUrl = $this->_oPaymentMethodsStub->getLogoPath();
        $this->assertContains("wirecard/paymentgateway/out/img/", $sLogoUrl);
    }

    public function getPublicFieldNames()
    {
        $aPublicFieldNames = $this->_oPaymentMethodsStub->getPublicFieldNames();

        $aExpected = [
            'apiUrl',
            'maid',
        ];

        $this->assertEquals($aExpected, $aPublicFieldNames);
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

    public function testOnBeforeOrderCreation()
    {
        $aResult = $this->_oPaymentMethodsStub->onBeforeTransactionCreation();
        $this->assertNull($aResult);
    }

    public function testGetHiddenAccountHolderFields()
    {
        $aResult = $this->_oPaymentMethodsStub->getHiddenAccountHolderFields();
        $this->assertEmpty($aResult);
    }
}
