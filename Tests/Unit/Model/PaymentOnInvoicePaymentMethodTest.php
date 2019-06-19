<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\PaymentOnInvoicePaymentMethod;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Transaction\PoiPiaTransaction;

class PaymentOnInvoicePaymentMethodTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var PaymentOnInvoicePaymentMethod
     */
    private $_oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->_oPaymentMethod = new PaymentOnInvoicePaymentMethod();
    }

    public function testGetConfig()
    {
        $oConfig = $this->_oPaymentMethod->getConfig();
        $this->assertInstanceOf(Config::class, $oConfig);
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $this->assertInstanceOf(PoiPiaTransaction::class, $oTransaction);
    }

    /**
     * @dataProvider getNameProvider
     */
    public function testGetName($bForOxid, $sExpected)
    {
        $sName = PaymentOnInvoicePaymentMethod::getName($bForOxid);
        $this->assertEquals($sExpected, $sName);
    }

    public function getNameProvider()
    {
        return [
            'for oxid' => [true, 'wdpaymentoninvoice'],
            'not for oxid' => [false, 'paymentoninvoice'],
        ];
    }

    public function testGetConfigFields()
    {
        $aFields = $this->_oPaymentMethod->getConfigFields();
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
        ], array_keys($aFields));
    }

    public function testGetPublicFieldNames()
    {
        $aPublicFieldNames = $this->_oPaymentMethod->getPublicFieldNames();
        $aExpected = [
            'apiUrl',
            'maid',
            'descriptor',
            'additionalInfo',
            'deleteCanceledOrder',
            'deleteFailedOrder',
        ];
        $this->assertEquals($aExpected, $aPublicFieldNames, '', 0.0, 1, true);
    }
}
