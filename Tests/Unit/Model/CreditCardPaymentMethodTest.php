<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\CreditCardPaymentMethod;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;

class CreditCardPaymentMethodTest extends OxidEsales\TestingLibrary\UnitTestCase
{

    /**
     * @var CreditCardPaymentMethod
     */
    private $_oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->_oPaymentMethod = new CreditCardPaymentMethod();
    }

    public function testGetConfig()
    {
        $oConfig = $this->_oPaymentMethod->getConfig();

        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('creditcard'));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $this->assertInstanceOf(CreditCardTransaction::class, $oTransaction);
    }

    public function testGetTransactionNotSettingNon3dCredentials()
    {
        $oConfig = $this->_oPaymentMethod->getConfig();

        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('creditcard'));

        /**
         * @var $oCreditCardConfig \Wirecard\PaymentSdk\Config\CreditCardConfig
         */
        $oCreditCardConfig = $oConfig->get('creditcard');

        $this->assertEquals("508b8896-b37d-4614-845c-26bf8bf2c948", $oCreditCardConfig->getThreeDMerchantAccountId());
        $this->assertEquals("dbc5a498-9a66-43b9-bf1d-a618dd399684", $oCreditCardConfig->getThreeDSecret());
        $this->assertNotNull($oCreditCardConfig->getMerchantAccountId());
        $this->assertNotNull($oCreditCardConfig->getSecret());
    }

    public function testGetTransactionNotSetting3dCredentials()
    {
        $oConfig = $this->_oPaymentMethod->getConfig();

        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('creditcard'));

        /**
         * @var $oCreditCardConfig \Wirecard\PaymentSdk\Config\CreditCardConfig
         */
        $oCreditCardConfig = $oConfig->get('creditcard');

        $this->assertEquals("53f2895a-e4de-4e82-a813-0d87a10e55e6", $oCreditCardConfig->getMerchantAccountId());
        $this->assertEquals("dbc5a498-9a66-43b9-bf1d-a618dd399684", $oCreditCardConfig->getSecret());
        $this->assertNotNull($oCreditCardConfig->getThreeDMerchantAccountId());
        $this->assertNotNull($oCreditCardConfig->getThreeDSecret());
    }

    public function testGetTransactionSettingNon3dMaxLimit()
    {
        $oConfig = $this->_oPaymentMethod->getConfig();

        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('creditcard'));

        /**
         * @var $oCreditCardConfig \Wirecard\PaymentSdk\Config\CreditCardConfig
         */
        $oCreditCardConfig = $oConfig->get('creditcard');

        $this->assertEquals(300, $oCreditCardConfig->getNonThreeDMaxLimit("EUR"));
        $this->assertNotEmpty($oCreditCardConfig->getThreeDMinLimit("EUR"));
    }

    public function testGetTransactionSetting3dMinLimit()
    {
        $oConfig = $this->_oPaymentMethod->getConfig();

        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('creditcard'));

        /**
         * @var $oCreditCardConfig \Wirecard\PaymentSdk\Config\CreditCardConfig
         */
        $oCreditCardConfig = $oConfig->get('creditcard');

        $this->assertEquals(100, $oCreditCardConfig->getThreeDMinLimit("EUR"));
        $this->assertNotEmpty($oCreditCardConfig->getNonThreeDMaxLimit("EUR"));
    }

    public function testGetConfigFields()
    {
        $aConfigFields = $this->_oPaymentMethod->getConfigFields();
        $this->assertArrayHasKey("threeDMaid", $aConfigFields);
        $this->assertArrayHasKey("threeDSecret", $aConfigFields);
        $this->assertArrayHasKey("nonThreeDMaxLimit", $aConfigFields);
        $this->assertArrayHasKey("threeDMinLimit", $aConfigFields);
        $this->assertArrayHasKey("limitsCurrency", $aConfigFields);
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $this->assertInstanceOf(CreditCardTransaction::class, $oTransaction);
    }

    public function testGetAdditionalConfigFieldsPaypal()
    {
        $aConfigFields = $this->_oPaymentMethod->getConfigFields();
        $this->assertCount(17, $aConfigFields);
        $this->assertArrayHasKey('threeDMaid', $aConfigFields);
        $this->assertArrayHasKey('threeDSecret', $aConfigFields);
        $this->assertArrayHasKey('nonThreeDMaxLimit', $aConfigFields);
        $this->assertArrayHasKey('threeDMinLimit', $aConfigFields);
        $this->assertArrayHasKey('limitsCurrency', $aConfigFields);
    }

    /**
     * @dataProvider testGetNameProvider
     */
    public function testGetName($bforOxid, $sExpected)
    {
        $sName = CreditCardPaymentMethod::getName($bforOxid);
        $this->assertEquals($sExpected, $sName);
    }

    public function testGetNameProvider()
    {
        return [
            'for oxid' => [true, 'wdcreditcard'],
            'not for oxid' => [false, 'creditcard'],
        ];
    }

    public function testGetPublicFieldNames()
    {
        $aPublicFieldNames = $this->_oPaymentMethod->getPublicFieldNames();
        $this->assertCount(11, $aPublicFieldNames);
        $aExpected = [
            "apiUrl",
            "maid",
            "threeDMaid",
            "nonThreeDMaxLimit",
            "threeDMinLimit",
            "descriptor",
            "limitsCurrency",
            "additionalInfo",
            "paymentAction",
            "deleteCanceledOrder",
            "deleteFailedOrder",
        ];

        $this->assertEquals($aExpected, $aPublicFieldNames, '', 0.0, 1, true);
    }
}
