<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Extend\Model\Basket;
use Wirecard\Oxid\Model\PaymentMethod\CreditCardPaymentMethod;
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

    /**
     * @dataProvider getConfigFieldsProvider
     */
    public function testGetConfigFields($sContainsKey)
    {
        $aConfigFields = $this->_oPaymentMethod->getConfigFields();
        $this->assertArrayHasKey($sContainsKey, $aConfigFields);
    }

    public function getConfigFieldsProvider() {
        return [
            "contains threeDMaid" => ['threeDMaid'],
            "contains threeDSecret" => ['threeDSecret'],
            "contains nonThreeDMaxLimit" => ['nonThreeDMaxLimit'],
            "contains limitsCurrency" => ['limitsCurrency'],
        ];
    }

    public function testGetName()
    {
        $sName = CreditCardPaymentMethod::getName();
        $this->assertEquals('wdcreditcard', $sName);
    }

    public function testGetPublicFieldNames()
    {
        $aPublicFieldNames = $this->_oPaymentMethod->getPublicFieldNames();
        $this->assertCount(15, $aPublicFieldNames);
        $aExpected = [
            "apiUrl",
            "apiUrlWpp",
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
            'oneClickEnabled',
            'oneClickChangedShipping',
            'challengeIndicator'
        ];

        $this->assertEquals($aExpected, $aPublicFieldNames, '', 0.0, 1, true);
    }

    public function testGetCheckoutFieldsNoOneclick()
    {
        $oUser = oxNew(User::class);
        $oUser->load('testuser');
        $oUser->oxuser__oxpassword = new Field('test1234');
        $oUser->oxuser__oxstateid = new Field('1');
        $oUser->save();
        Registry::getSession()->setUser($oUser);

        $oBasket = oxNew(Basket::class);
        Registry::getSession()->setBasket($oBasket);

        $oNoOneClick = new CreditCardPaymentMethod();

        $this->assertEquals([], $oNoOneClick->getCheckoutFields());
    }

    public function testGetCheckoutFieldsWithOneclick()
    {
        $oUser = oxNew(User::class);
        $oUser->load('testuser');
        $oUser->oxuser__oxpassword = new Field('test1234');
        $oUser->oxuser__oxcompany = new Field('Company');
        $oUser->oxuser__oxusername = new Field('Username');
        $oUser->oxuser__oxfname = new Field('Fname');
        $oUser->oxuser__oxlname = new Field('Lname');
        $oUser->oxuser__oxstreet = new Field('Street');
        $oUser->oxuser__oxstreetnr = new Field('Streetnr');
        $oUser->oxuser__oxaddinfo = new Field('Addinfo');
        $oUser->oxuser__oxustid = new Field('Ustid');
        $oUser->oxuser__oxcity = new Field('City');
        $oUser->oxuser__oxcountryid = new Field('Countryid');
        $oUser->oxuser__oxstateid = new Field('Stateid');
        $oUser->oxuser__oxzip = new Field('Zip');
        $oUser->oxuser__oxfon = new Field('Fon');
        $oUser->oxuser__oxfax = new Field('Fax');
        $oUser->oxuser__oxsal = new Field('Sal');
        $oUser->save();
        Registry::getSession()->setUser($oUser);

        $oBasket = oxNew(Basket::class);
        Registry::getSession()->setBasket($oBasket);

        $oOneClick = new CreditCardPaymentMethod();
        $oOneClick->getPayment()->oxpayments__oneclick_enabled = new Field('1');
        $oOneClick->getPayment()->save();

        $aResult = $oOneClick->getCheckoutFields();

        $aExpected = [
            'type',
            'data',
        ];

        $this->assertEquals($aExpected, array_keys($aResult[0]));
    }
}
