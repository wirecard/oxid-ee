<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\Oxid\Model\IdealPaymentMethod;
use Wirecard\Oxid\Model\SepaCreditTransferPaymentMethod;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\IdealTransaction;

use OxidEsales\Eshop\Core\Registry;

class IdealPaymentMethodTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var IdealPaymentMethod
     */
    private $_oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->_oPaymentMethod = new IdealPaymentMethod();
    }

    public function testGetConfig()
    {
        $oPayment = PaymentMethodHelper::getPaymentById(IdealPaymentMethod::getName(true));

        $oConfig = $this->_oPaymentMethod->getConfig();

        $this->assertInstanceOf(Config::class, $oConfig);
        $this->assertInstanceOf(PaymentMethodConfig::class, $oConfig->get(IdealPaymentMethod::getName()));
    }

    public function testGetTransaction()
    {
        $aDynArray = [
            'bank' => 'INGBNL2A',
        ];
        Registry::getSession()->setVariable('dynvalue', $aDynArray);
        $this->assertInstanceOf(IdealTransaction::class, $this->_oPaymentMethod->getTransaction());
    }

    /**
     * @dataProvider getNameProvider
     */
    /*public function testGetName($sExpected)
    {
        $sName = IdealPaymentMethod::getName();
        $this->assertEquals($sExpected, $sName);
    }

    public function getNameProvider()
    {
        return [
            'correct payment name' => ['ideal'],
        ];
    }

    public function testGetConfigFields()
    {
        $aConfigFields = $this->_oPaymentMethod->getConfigFields();
        $this->assertArrayHasKey('additionalInfo', $aConfigFields);
    }

    public function testGetCheckoutFields()
    {
        $aFields = $this->_oPaymentMethod->getCheckoutFields();
        $this->assertEquals(array_keys($aFields), [
            'bank',
        ]);
    }*/

    /**
     * @dataProvider getNameProvider
     */
    public function testGetName($bForOxid, $sExpected)
    {
        $sName = IdealPaymentMethod::getName($bForOxid);
        $this->assertEquals($sExpected, $sName);
    }

    public function getNameProvider()
    {
        return [
            'for oxid' => [true, 'wdideal'],
            'not for oxid' => [false, 'ideal'],
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
            "apiUrl",
            "maid",
            "descriptor",
            "additionalInfo",
            "deleteCanceledOrder",
            "deleteFailedOrder",
        ];
        $this->assertEquals($aExpected, $aPublicFieldNames, '', 0.0, 1, true);
    }

    public function testGetBanks()
    {
        $aBanks = $this->_oPaymentMethod->getBanks();
        $this->assertNotNull($aBanks);
    }

    public function testGetPublicFieldNames()
    {
        $aFieldNames = $this->_oPaymentMethod->getPublicFieldNames();
        $this->assertNotNull($aFieldNames);
    }

    public function testGetPostProcessingPaymentMethod()
    {
        $oTransaction = $this->_oPaymentMethod->getPostProcessingPaymentMethod('');
        $this->assertInstanceOf(SepaCreditTransferPaymentMethod::class, $oTransaction);
    }
}
