<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Field;

use Wirecard\Oxid\Model\SofortPaymentMethod;

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

    /**
     * @dataProvider testGetNameProvider
     */
    public function testGetName($bforOxid, $sExpected)
    {
        $sName = SofortPaymentMethod::getName($bforOxid);
        $this->assertEquals($sExpected, $sName);
    }

    public function testGetNameProvider()
    {
        return [
            'for oxid' => [true, 'wdsofortbanking'],
            'not for oxid' => [false, 'sofortbanking'],
        ];
    }

    public function testGetLogoPath()
    {
        $oPaymentStub = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oPaymentStub->method('__get')
            ->will($this->onConsecutiveCalls(new Field('CC=%s Variant=%s'), new Field('cc'), new Field('variant')));

        $sLogoUrl = $this->_oPaymentMethod->getLogoPath($oPaymentStub);
        $this->assertEquals('CC=cc Variant=variant', $sLogoUrl);
    }

    public function testGetConfigFields()
    {
        $aConfigFields = $this->_oPaymentMethod->getConfigFields();
        $this->assertCount(11, $aConfigFields);
        $this->assertArrayHasKey('additionalInfo', $aConfigFields);
        $this->assertArrayHasKey('deleteCanceledOrder', $aConfigFields);
        $this->assertArrayHasKey('deleteFailedOrder', $aConfigFields);
        $this->assertArrayHasKey('countryCode', $aConfigFields);
        $this->assertArrayHasKey('logoType', $aConfigFields);
    }

    public function testGetPublicFieldNames()
    {
        $aPublicFields = $this->_oPaymentMethod->getPublicFieldNames();
        $this->assertCount(7, $aPublicFields);
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
