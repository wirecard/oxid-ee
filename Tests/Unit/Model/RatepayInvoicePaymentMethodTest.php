<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\RatepayInvoicePaymentMethod;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;

class RatepayInvoicePaymentMethodTest extends OxidEsales\TestingLibrary\UnitTestCase
{

    /**
     * @var RatepayInvoicePaymentMethod
     */
    private $_oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->_oPaymentMethod = new RatepayInvoicePaymentMethod();
    }

    public function testGetConfig()
    {
        $oConfig = $this->_oPaymentMethod->getConfig();

        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('ratepay-invoice'));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $this->assertInstanceOf(RatepayInvoiceTransaction::class, $oTransaction);
    }

    public function testIsMerchantOnly()
    {
        $this->assertFalse($this->_oPaymentMethod->isMerchantOnly());
    }

    /**
     * @dataProvider getNameProvider
     */
    public function testGetName($bforOxid, $sExpected)
    {
        $sName = RatepayInvoicePaymentMethod::getName($bforOxid);
        $this->assertEquals($sExpected, $sName);
    }

    public function getNameProvider()
    {
        return [
            'for oxid' => [true, 'wdratepay-invoice'],
            'not for oxid' => [false, 'ratepay-invoice'],
        ];
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
        ];
    }

    public function testGetConfigFieldsCount()
    {
        $aFieldKeys = array_keys($this->_oPaymentMethod->getConfigFields());

        $this->assertEquals([
            'apiUrl',
            'httpUser',
            'httpPassword',
            'testCredentials',
            'maid',
            'secret',
            'descriptor',
            'additionalInfo',
            'deleteCanceledOrder',
            'deleteFailedOrder',
            'allowedCurrencies',
        ], $aFieldKeys);
    }

    public function testGetPublicFieldNames()
    {
        $aPublicFields = $this->_oPaymentMethod->getPublicFieldNames();
        $aExpected = [
            'apiUrl',
            'maid',
            'descriptor',
            'additionalInfo',
            'deleteCanceledOrder',
            'deleteFailedOrder',
        ];
        $this->assertEquals($aExpected, $aPublicFields, '', 0.0, 1, true);
    }
}
