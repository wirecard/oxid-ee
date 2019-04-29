<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 *
 */

use Wirecard\Oxid\Extend\ViewConfig;

class ViewConfigTest extends OxidEsales\TestingLibrary\UnitTestCase
{

    /**
     * @var \Wirecard\Oxid\Extend\ViewConfig
     */
    private $_oViewConfig;

    protected function setUp()
    {
        parent::setUp();
        $this->_oViewConfig = oxNew(ViewConfig::class);
    }

    public function testModuleDeviceId()
    {
        $sMaid = "test Merchant Id";
        $this->assertTrue(strpos($this->_oViewConfig->getModuleDeviceId($sMaid), $sMaid) === 0);
    }

    public function testGetPaymentGatewayUrl()
    {
        $sPaymentGatewayUrl = $this->_oViewConfig->getPaymentGatewayUrl('Tests/resources/success_response.xml');
        $this->assertContains('wirecard/paymentgateway/Tests/resources/success_response.xml', $sPaymentGatewayUrl);
    }

    /**
     * @dataProvider testIsThisModuleProvider
     */
    public function testIsThisModule($sModuleName, $bExpected)
    {
        $bResult = $this->_oViewConfig->isThisModule($sModuleName);
        $this->assertEquals($bExpected, $bResult);
    }

    public function testIsThisModuleProvider()
    {
        return [
            'is our module' => ['wdoxidee', true],
            'is not our module' => ['fake', false],
        ];
    }
}
