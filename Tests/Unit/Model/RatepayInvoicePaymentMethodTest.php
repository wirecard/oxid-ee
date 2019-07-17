<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\PaymentMethod\RatepayInvoicePaymentMethod;
use Wirecard\Oxid\Extend\Model\Payment;
use Wirecard\Oxid\Model\Transaction;

use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;

use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Exception\InputException;

use Wirecard\Oxid\Tests\Unit\Controller\Admin\TestDataHelper;

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

    protected function dbData()
    {
        return [
            [
                'table' => 'oxuser',
                'columns' => ['oxid', 'oxpassword', 'oxbirthdate', 'oxfon'],
                'rows' => [
                    ['testuser', 'testpassword', '12.12.1985', '45646846'],
                ]
            ],
        ];
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

    public function testGetName()
    {
        $sName = RatepayInvoicePaymentMethod::getName();
        $this->assertEquals('wdratepay-invoice', $sName);
    }

    /**
     * @dataProvider getCheckoutFieldsProvider
     */
    public function testGetCheckoutFields($aValues, $aExpected, $bGuestUser)
    {
        $oUser = oxNew(User::class);
        $oUser->load('testuser');
        $oUser->oxuser__oxpassword = new Field($bGuestUser ? '' : 'testpassword');
        $oUser->save();
        $this->getSession()->setUser($oUser);

        $aDynvalues['dateOfBirthwdratepay-invoice'] = $aValues['dateOfBirth'];
        $aDynvalues['phonewdratepay-invoice'] = $aValues['phone'];
        $this->getSession()->setVariable('dynvalue', $aDynvalues);

        $aFields = $this->_oPaymentMethod->getCheckoutFields();

        foreach ($aFields as $sKey => $aValue) {
            if ($aValue['type'] === 'hidden') {
                unset($aFields[$sKey]);
            }
        }

        $this->assertEquals($aExpected, array_keys($aFields));
    }

    public function getCheckoutFieldsProvider()
    {
        return [
            'nothing set' => [
                ['dateOfBirth' => '', 'phone' => ''],
                ['dateOfBirthwdratepay-invoice', 'phonewdratepay-invoice', 'saveCheckoutFieldswdratepay-invoice'],
                false,
            ],
            'date of birth set' => [
                ['dateOfBirth' => '12.12.1985', 'phone' => ''],
                ['phonewdratepay-invoice', 'saveCheckoutFieldswdratepay-invoice'],
                false,
            ],
            'phone set' => [
                ['dateOfBirth' => '', 'phone' => '324324234'],
                ['dateOfBirthwdratepay-invoice', 'saveCheckoutFieldswdratepay-invoice'],
                false,
            ],
            'both set' => [
                ['dateOfBirth' => '12.12.1985', 'phone' => '324324234'],
                [],
                false,
            ],
            'guest user nothing set' => [
                ['dateOfBirth' => '', 'phone' => ''],
                ['dateOfBirthwdratepay-invoice', 'phonewdratepay-invoice'],
                true,
            ],
            'guest user date of birth set' => [
                ['dateOfBirth' => '12.12.1985', 'phone' => ''],
                ['phonewdratepay-invoice'],
                true,
            ],
            'guest user phone set' => [
                ['dateOfBirth' => '', 'phone' => '324324234'],
                ['dateOfBirthwdratepay-invoice'],
                true,
            ],
            'guest user both set' => [
                ['dateOfBirth' => '12.12.1985', 'phone' => '324324234'],
                [],
                true,
            ],
        ];
    }

    public function testAddMandatoryTransactionData()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $oOrder = oxNew(Order::class);
        $oOrder->oxorder__oxpaymenttype = new Field(RatepayInvoicePaymentMethod::getName());
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

        $oUser = oxNew(User::class);
        $oUser->load('testuser');
        $oUser->oxuser__oxcountryid = new Field($sBillingCountryId);
        $oUser->save();
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
            $this->setSessionParam('dynvalue', ['dateOfBirthwdratepay-invoice' => $sUserDateOfBirth]);
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

        $this->getSession()->setUser($oUser);

        if ($sShippingCountryId) {
            $oAddress = oxNew(Address::class);
            $oAddress->oxaddress__oxcountryid = new Field($sShippingCountryId);
            $oAddress->save();

            $this->setSessionParam('deladrid', $oAddress->getId());
        }

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

    public function testOnBeforeOrderCreation()
    {
        $oUser = oxNew(User::class);
        $oUser->load('testuser');
        $oUser->save();
        $this->getSession()->setUser($oUser);

        $aDynvalues['dateOfBirthwdratepay-invoice'] = '12.12.1985';
        $aDynvalues['phonewdratepay-invoice'] = '65161651';
        $aDynvalues['saveCheckoutFieldswdratepay-invoice'] = '1';
        $this->getSession()->setVariable('dynvalue', $aDynvalues);

        try {
            $this->_oPaymentMethod->onBeforeOrderCreation();
        } catch (InputException $exception) {
            $this->fail("Exception thrown: " . get_class($exception));
        }
    }

    /**
     * @dataProvider onBeforeOrderCreationFailedProvider
     * @expectedException \OxidEsales\Eshop\Core\Exception\InputException
     */
    public function testOnBeforeOrderCreationFailed($aValues)
    {
        $oUser = oxNew(User::class);
        $oUser->load('testuser');
        $oUser->save();
        $this->getSession()->setUser($oUser);

        $aDynvalues['dateOfBirthwdratepay-invoice'] = $aValues['dateOfBirth'];
        $aDynvalues['phonewdratepay-invoice'] = $aValues['phone'];
        $this->getSession()->setVariable('dynvalue', $aDynvalues);

        $this->_oPaymentMethod->onBeforeOrderCreation();
    }

    public function onBeforeOrderCreationFailedProvider()
    {
        return [
            'date of birth invalid' => [
                ['dateOfBirth' => '', 'phone' => '65161651'],
            ],
            'phone invalid' => [
                ['dateOfBirth' => '12.12.1985', 'phone' => ''],
            ],
        ];
    }

    /**
     * @dataProvider getPostProcessingTransactionProvider
     */
    public function testGetPostProcessingTransaction($aOrderItems)
    {
        $oParentTransaction = $this->getResponseXMLTransaction();

        $oResult = $this->_oPaymentMethod->getPostProcessingTransaction(Transaction::ACTION_CREDIT, $oParentTransaction, $aOrderItems);

        $this->assertInstanceOf(RatepayInvoiceTransaction::class, $oResult);
    }

    public function getPostProcessingTransactionProvider()
    {
        return [
            'articles left' => [
                ['Article Number' => 1],
            ],
            'no articles left' => [
                ['Article Number' => 0],
            ],
            'other article left' => [
                ['Other Article' => 1],
            ],
        ];
    }

    /**
     * @dataProvider getPostProcessingTransactionQuantityProvider
     */
    public function testGetPostProcessingTransactionQuantity($aOrderItems, $iExpected)
    {
        $oParentTransaction = $this->getResponseXMLTransaction();

        $oResult = $this->_oPaymentMethod->getPostProcessingTransaction(Transaction::ACTION_CREDIT, $oParentTransaction, $aOrderItems);
        $oResult->setOperation(Transaction::ACTION_CREDIT);

        $this->assertEquals($iExpected, $oResult->mappedProperties()['order-items']['order-item'][0]['quantity']);
    }

    public function getPostProcessingTransactionQuantityProvider()
    {
        return [
            'articles left' => [
                ['Article Number' => 1],
                1
            ],
            'no articles left' => [
                ['Article Number' => 0],
                null
            ],
        ];
    }

    /**
     * @dataProvider getPostProcessingTransactionArticleProvider
     */
    public function testGetPostProcessingTransactionArticle($aOrderItems, $sExpected)
    {
        $oParentTransaction = $this->getResponseXMLTransaction();

        $oResult = $this->_oPaymentMethod->getPostProcessingTransaction(Transaction::ACTION_CREDIT, $oParentTransaction, $aOrderItems);
        $oResult->setOperation(Transaction::ACTION_CREDIT);

        $this->assertEquals($sExpected, $oResult->mappedProperties()['order-items']['order-item'][0]['article-number']);
    }

    public function getPostProcessingTransactionArticleProvider()
    {
        return [
            'articles left' => [
                ['Article Number' => 1],
                'Article Number'
            ],
            'no articles left' => [
                ['Article Number' => 0],
                null
            ],
        ];
    }

    private function getResponseXMLTransaction()
    {
        $oTransaction = $this->getMockBuilder(RatepayInvoiceTransaction::class)
            ->setMethods(['getResponseXML'])
            ->getMock();
        $oTransaction
            ->method('getResponseXML')
            ->willReturn(TestDataHelper::getSuccessXmlResponse());

        return $oTransaction;
    }
}
