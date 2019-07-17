<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\PaymentMethod\SofortPaymentMethod;
use Wirecard\PaymentSdk\Transaction\SofortTransaction;

class SofortPaymentMethodTest extends OxidEsales\TestingLibrary\UnitTestCase
{

    /**
     * @var SofortPaymentMethod
     */
    private $_oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->_oPaymentMethod = new SofortPaymentMethod();
    }

    public function testGetConfig()
    {
        $oConfig = $this->_oPaymentMethod->getConfig();

        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('sofortbanking'));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $this->assertInstanceOf(SofortTransaction::class, $oTransaction);
    }

    public function testIsMerchantOnly()
    {
        $this->assertFalse($this->_oPaymentMethod->isMerchantOnly());
    }

    public function testGetName()
    {
        $sName = SofortPaymentMethod::getName();
        $this->assertEquals('wdsofortbanking', $sName);
    }

    public function testGetLogoPath()
    {
        $sLogoUrl = $this->_oPaymentMethod->getLogoPath();
        $this->assertContains('en_gb/pay_now/standard', $sLogoUrl);
    }

    /**
     * @dataProvider configFieldsProvider
     */
    public function testGetConfigFields($sContainsKey)
    {
        $aConfigFields = $this->_oPaymentMethod->getConfigFields();
        $this->assertArrayHasKey($sContainsKey, $aConfigFields);
    }

    public function configFieldsProvider()
    {
        return [
            "contains additionalInfo" => ['additionalInfo'],
            "contains deleteCanceledOrder" => ['deleteCanceledOrder'],
            "contains deleteFailedOrder" => ['deleteFailedOrder'],
            "contains countryCode" => ['countryCode'],
            "contains logoType" => ['logoType'],
        ];
    }

    public function testGetConfigFieldsCount()
    {
        $aConfigFields = $this->_oPaymentMethod->getConfigFields();
        $this->assertCount(11, $aConfigFields);

    }

    public function testGetPublicFieldNames()
    {
        $aPublicFields = $this->_oPaymentMethod->getPublicFieldNames();
        $aExpected = [
            'apiUrl',
            'maid',
            'additionalInfo',
            'countryCode',
            'logoType',
            'deleteCanceledOrder',
            'deleteFailedOrder',
        ];
        $this->assertEquals($aExpected, $aPublicFields, '', 0.0, 1, true);
    }
}
