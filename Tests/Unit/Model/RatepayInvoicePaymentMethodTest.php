<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Application\Model\Order;

use Wirecard\Oxid\Model\RatepayInvoicePaymentMethod;
use Wirecard\Oxid\Extend\Model\Payment;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;

use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;

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

    public function testAddMandatoryTransactionData()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $oOrder = oxNew(Order::class);
        $this->_oPaymentMethod->addMandatoryTransactionData($oTransaction, $oOrder);

        $this->assertObjectHasAttribute('shipping', $oTransaction);
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
            'allowedCurrencies',
            'shippingCountries',
            'billingCountries',
            'billingShipping',
        ], array_keys($aFields));
    }

    public function testGetPublicFieldNames()
    {
        $aFieldNames = $this->_oPaymentMethod->getPublicFieldNames();

        $this->assertEquals([
            'apiUrl',
            'maid',
            'descriptor',
            'additionalInfo',
            'deleteCanceledOrder',
            'deleteFailedOrder',
            'allowedCurrencies',
            'shippingCountries',
            'billingCountries',
            'billingShipping',
        ], $aFieldNames);
    }

    public function testGetMetaDataFieldNames()
    {
        $this->assertEquals([
            'allowed_currencies',
            'shipping_countries',
            'billing_countries',
            'billing_shipping',
        ], $this->_oPaymentMethod->getMetaDataFieldNames());
    }

    /**
     * @dataProvider isPaymentPossibleProvider
     */
    public function testIsPaymentPossible(
        $blExpected,
        $sUserDateOfBirth,
        $blHasDigitalProducts,
        $sCurrency,
        $sBillingCountryId,
        $sShippingCountryId
    ) {
        $oPaymentMethodStub = $this->getMockBuilder(RatepayInvoicePaymentMethod::class)
            ->setMethods(['getPayment'])
            ->getMock();
        $oBasketStub = $this->getMockBuilder(Basket::class)
            ->setMethods(['getBasketArticles'])
            ->getMock();
        $oUserStub = $this->getMockBuilder(User::class)
            ->setMethods(['getSelectedAddress'])
            ->getMock();
        $oPayment = oxNew(Payment::class);

        // configure the payment
        $oPayment->oxpayments__allowed_currencies = new Field(['EUR']);
        $oPayment->oxpayments__billing_countries = new Field(['DE']);
        $oPayment->oxpayments__shipping_countries = new Field(['DE']);
        $oPayment->oxpayments__billing_shipping = new Field('0');

        $oPaymentMethodStub
            ->method('getPayment')
            ->willReturn($oPayment);

        // set the user's date of birth to the session
        if ($sUserDateOfBirth) {
            $this->setSessionParam('dynvalue', ['dateOfBirth' => $sUserDateOfBirth]);
        }

        // create a mock article and add it to the basket
        $oArticle = oxNew(Article::class);
        $oArticle->oxarticles__oxisdownloadable = new Field($blHasDigitalProducts);

        $oBasketStub
            ->method('getBasketArticles')
            ->willReturn([$oArticle]);

        // set the currency to the basket
        $oBasketStub->setBasketCurrency((object) ['name' => $sCurrency]);

        $this->getSession()->setBasket($oBasketStub);

        // create mock addresses and add them to the user/session
        $oAddress = oxNew(Address::class);
        $oAddress->oxaddress__oxcountryid = new Field($sBillingCountryId);

        $oUserStub
            ->method('getSelectedAddress')
            ->willReturn($oAddress);

        if ($sShippingCountryId) {
            $this->setSessionParam('deladrid', $sShippingCountryId);
        }

        $this->getSession()->setUser($oUserStub);

        $this->assertEquals($blExpected, $oPaymentMethodStub->isPaymentPossible());
    }

    public function isPaymentPossibleProvider()
    {
        return [
            'user without set age' => [true, null, false, 'EUR', 'a7c40f631fc920687.20179984', null],
            'user below 18 years' => [false, date('d.m.Y'), false, 'EUR', 'a7c40f631fc920687.20179984', null],
            'user above 18 years' => [true, '01.01.2000', false, 'EUR', 'a7c40f631fc920687.20179984', null],
            'with digital products in basket' => [false, null, true, 'EUR', 'a7c40f631fc920687.20179984', null],
            'with disallowed currency' => [false, null, false, 'USD', 'a7c40f631fc920687.20179984', null],
            'with disallowed billing country' => [false, null, false, 'EUR', 'a7c40f6320aeb2ec2.72885259', null],
            'with disallowed shipping country' => [false, null, false, 'EUR', 'a7c40f631fc920687.20179984', 'a7c40f6320aeb2ec2.72885259'],
        ];
    }
}
