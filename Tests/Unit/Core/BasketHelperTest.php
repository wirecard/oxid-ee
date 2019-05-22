<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\BasketItem;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Price;

use PHPUnit\Framework\MockObject\MockObject;

use Wirecard\Oxid\Core\BasketHelper;
use Wirecard\Oxid\Core\Helper;
use Wirecard\PaymentSdk\Entity\Basket as TransactionBasket;

class BasketHelperTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var Price|MockObject
     */
    private $_oPriceStub;

    /**
     * @var Basket|MockObject
     */
    private $_oBasketStub;

    public function setUp()
    {
        parent::setUp();

        $this->_oPriceStub = $this->getMockBuilder(Price::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_oPriceStub->method('getPrice')
            ->willReturn(100.0);
        $this->_oPriceStub->method('getBruttoPrice')
            ->willReturn(100.9999);
        $this->_oPriceStub->method('getVat')
            ->willReturn(20);
        $this->_oPriceStub->method('getVatValue')
            ->willReturn(20);

        $this->_oBasketStub = $this->getMockBuilder(Basket::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testArticlesSetInTheBasket()
    {
        $product1Stub = $this->getMockBuilder(Article::class)
            ->disableOriginalConstructor()
            ->getMock();

        $articleName = new Field('Article name');
        $articleNum = new Field("Article number");
        $articleDesc = new Field('Article desc');

        $product1Stub->method('__get')
            ->will($this->onConsecutiveCalls($articleName, $articleNum, $articleDesc));

        $basketItem1Stub = $this->getMockBuilder(BasketItem::class)
            ->getMock();
        $basketItem1Stub->expects($this->any())
            ->method('getArticle')
            ->willReturn($product1Stub);
        $basketItem1Stub->expects($this->any())
            ->method('getUnitPrice')
            ->willReturn($this->_oPriceStub);
        $basketItem1Stub->expects($this->any())
            ->method('getAmount')
            ->willReturn(1);

        $wdBasket = new TransactionBasket();

        BasketHelper::addArticleToBasket($wdBasket, $basketItem1Stub, "EUR");

        $wdBasketIterator = $wdBasket->getIterator();
        $wdBasketIterator->seek(0);
        $aCurrentItem = $wdBasketIterator->current()->mappedProperties();

        $aExpected = [
            'name' => 'Article name',
            'quantity' => 1,
            'amount' => [
                'currency' => 'EUR',
                'value' => 101.0,
            ],
            'description' => 'Article desc',
            'article-number' => 'Article number',
            'tax-rate' => 20,
        ];

        $this->assertEquals($aExpected, $aCurrentItem);
    }

    public function testPaymentCostForTheCurrentBasket()
    {
        $this->_oBasketStub->expects($this->once())
            ->method('getPaymentCost')
            ->willReturn($this->_oPriceStub);

        $wdBasket = new TransactionBasket();
        BasketHelper::addPaymentCostsToBasket($wdBasket, $this->_oBasketStub, "EUR");

        $wdBasketIterator = $wdBasket->getIterator();
        $wdBasketIterator->seek(0);
        $aCurrentItem = $wdBasketIterator->current()->mappedProperties();

        $aExpected = [
            'name' => Helper::translate('wd_payment_cost'),
            'quantity' => 1,
            'amount' => [
                'currency' => 'EUR',
                'value' => 101.0,
            ],
            'description' => Helper::translate('wd_payment_cost'),
            'article-number' => Helper::translate('wd_payment_cost'),
            'tax-rate' => 20,
        ];

        $this->assertEquals($aExpected, $aCurrentItem);
    }

    public function testGiftCardCostAddedToTheBasket()
    {
        $this->_oBasketStub->expects($this->any())
            ->method('getGiftCardCost')
            ->willReturn($this->_oPriceStub);

        $wdBasket = new TransactionBasket();
        BasketHelper::addGiftCardCostsToBasket($wdBasket, $this->_oBasketStub, "EUR");

        $wdBasketIterator = $wdBasket->getIterator();
        $wdBasketIterator->seek(0);
        $aCurrentItem = $wdBasketIterator->current()->mappedProperties();

        $aExpected = [
            'name' => Helper::translate('GREETING_CARD'),
            'quantity' => 1,
            'amount' => [
                'currency' => 'EUR',
                'value' => 101.0,
            ],
            'description' => Helper::translate('GREETING_CARD'),
            'article-number' => Helper::translate('GREETING_CARD'),
            'tax-rate' => 20,
        ];

        $this->assertEquals($aExpected, $aCurrentItem);
    }

    public function testWrappingCostAddedToTheBasket()
    {
        $this->_oBasketStub->expects($this->once())
            ->method('getWrappingCost')
            ->willReturn($this->_oPriceStub);

        $wdBasket = new TransactionBasket();
        BasketHelper::addWrappingCostsToBasket($wdBasket, $this->_oBasketStub, "EUR");

        $wdBasketIterator = $wdBasket->getIterator();
        $wdBasketIterator->seek(0);
        $aCurrentItem = $wdBasketIterator->current()->mappedProperties();

        $aExpected = [
            'name' => Helper::translate('WRAPPING'),
            'quantity' => 1,
            'amount' => [
                'currency' => 'EUR',
                'value' => 101.0,
            ],
            'description' => Helper::translate('WRAPPING'),
            'article-number' => Helper::translate('WRAPPING'),
            'tax-rate' => 20,
        ];

        $this->assertEquals($aExpected, $aCurrentItem);
    }

    public function testVoucherDiscountsCostAddedToTheBasket()
    {
        $voucher = new stdClass();
        $voucher->dVoucherdiscount = 25.0;

        $this->_oBasketStub->expects($this->once())
            ->method('getVouchers')
            ->willReturn(array($voucher));

        $wdBasket = new TransactionBasket();
        BasketHelper::addVoucherDiscountsToBasket($wdBasket, $this->_oBasketStub, "EUR");

        $wdBasketIterator = $wdBasket->getIterator();
        $wdBasketIterator->seek(0);
        $aCurrentItem = $wdBasketIterator->current()->mappedProperties();

        $aExpected = [
            'name' => Helper::translate('COUPON'),
            'quantity' => 1,
            'amount' => [
                'currency' => 'EUR',
                'value' => -25,
            ],
            'description' => Helper::translate('COUPON'),
            'article-number' => Helper::translate('COUPON'),
        ];

        $this->assertEquals($aExpected, $aCurrentItem);
    }

    public function testShippingCostAddedToTheBasket()
    {
        $this->_oBasketStub->expects($this->once())
            ->method('getDeliveryCost')
            ->willReturn($this->_oPriceStub);

        $wdBasket = new TransactionBasket();
        BasketHelper::addShippingCostsToBasket($wdBasket, $this->_oBasketStub, "USD");

        $wdBasketIterator = $wdBasket->getIterator();
        $wdBasketIterator->seek(0);
        $aCurrentItem = $wdBasketIterator->current()->mappedProperties();

        $aExpected = [
            'name' => Helper::translate('wd_shipping_title'),
            'quantity' => 1,
            'amount' => [
                'currency' => 'USD',
                'value' => 101.0,
            ],
            'description' => Helper::translate('wd_shipping_title'),
            'article-number' => Helper::translate('wd_shipping_title'),
            'tax-rate' => 20,
        ];

        $this->assertEquals($aExpected, $aCurrentItem);
    }

    public function testNoShippingCostForTheBasket()
    {
        $this->_oBasketStub->expects($this->once())
            ->method('getDeliveryCost')
            ->willReturn(false);

        $wdBasket = new TransactionBasket();
        BasketHelper::addShippingCostsToBasket($wdBasket, $this->_oBasketStub, $this->_oCurrency);

        $wdBasketIterator = $wdBasket->getIterator();

        $wdBasketIterator->seek(0);
        $aCurrentItem = $wdBasketIterator->current()->mappedProperties();

        $aExpected = [
            'name' => Helper::translate('wd_shipping_title'),
            'quantity' => 1,
            'amount' => [
                'value' => 0.0,
                'currency' => null,
            ],
            'description' => Helper::translate('wd_shipping_title'),
            'article-number' => Helper::translate('wd_shipping_title'),
            'tax-rate' => 0,
        ];

        $this->assertEquals($aExpected, $aCurrentItem);
    }

    public function testDiscountsAddedToTheBasket()
    {
        $discount = new stdClass();
        $discount->sDiscount = 'Title';
        $discount->dDiscount = 10;
        $discount->sOXID = 'Discount ID';

        $this->_oBasketStub->expects($this->once())
            ->method('getDiscounts')
            ->willReturn(array($discount));

        $wdBasket = new TransactionBasket();
        BasketHelper::addDiscountsToBasket($wdBasket, $this->_oBasketStub, "EUR");

        $wdBasketIterator = $wdBasket->getIterator();
        $wdBasketIterator->seek(0);
        $aCurrentItem = $wdBasketIterator->current()->mappedProperties();

        $aExpected = [
            'name' => 'Title',
            'quantity' => 1,
            'amount' => [
                'value' => -10.0,
                'currency' => 'EUR'
            ],
            'description' => 'Title',
            'article-number' => 'Discount ID',
        ];

        $this->assertEquals($aExpected, $aCurrentItem);
    }
}
