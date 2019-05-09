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

use Wirecard\Oxid\Core\BasketHelper;
use Wirecard\Oxid\Core\Helper;

use Wirecard\PaymentSdk\Entity\Basket as TransactionBasket;

class BasketHelperTest extends OxidEsales\TestingLibrary\UnitTestCase
{

    private $priceStub;
    private $basketStub;
    private $currency;

    public function setUp()
    {
        parent::setUp();

        $this->priceStub = $this->getMockBuilder(Price::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->priceStub->method('getPrice')
            ->willReturn(100.0);
        $this->priceStub->method('getBruttoPrice')
            ->willReturn(100.9999);
        $this->priceStub->method('getVat')
            ->willReturn(20);
        $this->priceStub->method('getVatValue')
            ->willReturn(20);

        $this->basketStub = $this->getMockBuilder(Basket::class)
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
            ->willReturn($this->priceStub);
        $basketItem1Stub->expects($this->any())
            ->method('getAmount')
            ->willReturn(1);

        $wdBasket = new TransactionBasket();

        BasketHelper::addArticleToBasket($wdBasket, $basketItem1Stub, "EUR");

        $wdBasketIterator = $wdBasket->getIterator();
        $this->assertTrue($wdBasketIterator->count() == 1);

        $wdBasketIterator->seek(0);
        $currentItem = $wdBasketIterator->current()->mappedProperties();

        $this->assertEquals('Article name', $currentItem['name']);
        $this->assertEquals(1, $currentItem['quantity']);
        $this->assertEquals('EUR', $currentItem['amount']['currency']);
        $this->assertEquals(101.0, $currentItem['amount']['value']);
        $this->assertEquals('Article desc', $currentItem['description']);
        $this->assertEquals('Article number', $currentItem['article-number']);
        $this->assertEquals(20, $currentItem['tax-rate']);
    }

    public function testPaymentCostForTheCurrentBasket()
    {
        $this->basketStub->expects($this->once())
            ->method('getPaymentCost')
            ->willReturn($this->priceStub);

        $wdBasket = new TransactionBasket();
        BasketHelper::addPaymentCostsToBasket($wdBasket, $this->basketStub, "EUR");

        $wdBasketIterator = $wdBasket->getIterator();
        $this->assertEquals(1, $wdBasketIterator->count());

        $wdBasketIterator->seek(0);
        $currentItem = $wdBasketIterator->current()->mappedProperties();

        $this->assertEquals(Helper::translate('wd_payment_cost'), $currentItem['name']);
        $this->assertEquals(1, $currentItem['quantity']);
        $this->assertEquals('EUR', $currentItem['amount']['currency']);
        $this->assertEquals(101.0, $currentItem['amount']['value']);
        $this->assertEquals(Helper::translate('wd_payment_cost'), $currentItem['description']);
        $this->assertEquals(Helper::translate('wd_payment_cost'), $currentItem['article-number']);
        $this->assertEquals(20, $currentItem['tax-rate']);
    }

    public function testGiftCardCostAddedToTheBasket()
    {
        $this->basketStub->expects($this->any())
            ->method('getGiftCardCost')
            ->willReturn($this->priceStub);

        $wdBasket = new TransactionBasket();
        BasketHelper::addGiftCardCostsToBasket($wdBasket, $this->basketStub, "EUR");

        $wdBasketIterator = $wdBasket->getIterator();
        $this->assertEquals(1, $wdBasketIterator->count());

        $wdBasketIterator->seek(0);
        $currentItem = $wdBasketIterator->current()->mappedProperties();

        $this->assertEquals(Helper::translate('GREETING_CARD'), $currentItem['name']);
        $this->assertEquals(1, $currentItem['quantity']);
        $this->assertEquals('EUR', $currentItem['amount']['currency']);
        $this->assertEquals(101.0, $currentItem['amount']['value']);
        $this->assertEquals(Helper::translate('GREETING_CARD'), $currentItem['description']);
        $this->assertEquals(Helper::translate('GREETING_CARD'), $currentItem['article-number']);
        $this->assertEquals(20, $currentItem['tax-rate']);
    }

    public function testWrappingCostAddedToTheBasket()
    {
        $this->basketStub->expects($this->once())
            ->method('getWrappingCost')
            ->willReturn($this->priceStub);

        $wdBasket = new TransactionBasket();
        BasketHelper::addWrappingCostsToBasket($wdBasket, $this->basketStub, "EUR");

        $wdBasketIterator = $wdBasket->getIterator();
        $this->assertEquals(1, $wdBasketIterator->count());

        $wdBasketIterator->seek(0);
        $currentItem = $wdBasketIterator->current()->mappedProperties();

        $this->assertEquals(Helper::translate('WRAPPING'), $currentItem['name']);
        $this->assertEquals(1, $currentItem['quantity']);
        $this->assertEquals('EUR', $currentItem['amount']['currency']);
        $this->assertEquals(101.0, $currentItem['amount']['value']);
        $this->assertEquals(Helper::translate('WRAPPING'), $currentItem['description']);
        $this->assertEquals(Helper::translate('WRAPPING'), $currentItem['article-number']);
        $this->assertEquals(20, $currentItem['tax-rate']);
    }

    public function testVoucherDiscountsCostAddedToTheBasket()
    {
        $voucher = new stdClass();
        $voucher->dVoucherdiscount = 25.0;

        $this->basketStub->expects($this->once())
            ->method('getVouchers')
            ->willReturn(array($voucher));

        $wdBasket = new TransactionBasket();
        BasketHelper::addVoucherDiscountsToBasket($wdBasket, $this->basketStub, "EUR");

        $wdBasketIterator = $wdBasket->getIterator();
        $this->assertEquals(1, $wdBasketIterator->count());

        $wdBasketIterator->seek(0);
        $currentItem = $wdBasketIterator->current()->mappedProperties();

        $this->assertEquals(Helper::translate('COUPON'), $currentItem['name']);
        $this->assertEquals(1, $currentItem['quantity']);
        $this->assertEquals('EUR', $currentItem['amount']['currency']);
        $this->assertEquals(-25.0, $currentItem['amount']['value']);
        $this->assertEquals(Helper::translate('COUPON'), $currentItem['description']);
        $this->assertEquals(Helper::translate('COUPON'), $currentItem['article-number']);
        $this->assertNull($currentItem['tax-rate']);
    }

    public function testShippingCostAddedToTheBasket()
    {
        $this->basketStub->expects($this->once())
            ->method('getDeliveryCost')
            ->willReturn($this->priceStub);

        $wdBasket = new TransactionBasket();
        BasketHelper::addShippingCostsToBasket($wdBasket, $this->basketStub, "USD");

        $wdBasketIterator = $wdBasket->getIterator();
        $this->assertEquals(1, $wdBasketIterator->count());

        $wdBasketIterator->seek(0);
        $currentItem = $wdBasketIterator->current()->mappedProperties();

        $this->assertEquals(Helper::translate('wd_shipping_title'), $currentItem['name']);
        $this->assertEquals(1, $currentItem['quantity']);
        $this->assertEquals('USD', $currentItem['amount']['currency']);
        $this->assertEquals(101.0, $currentItem['amount']['value']);
        $this->assertEquals(Helper::translate('wd_shipping_title'), $currentItem['description']);
        $this->assertEquals(Helper::translate('wd_shipping_title'), $currentItem['article-number']);
        $this->assertEquals(20, $currentItem['tax-rate']);
    }

    public function testNoShippingCostForTheBasket()
    {
        $this->basketStub->expects($this->once())
            ->method('getDeliveryCost')
            ->willReturn(false);

        $wdBasket = new TransactionBasket();
        BasketHelper::addShippingCostsToBasket($wdBasket, $this->basketStub, $this->currency);

        $wdBasketIterator = $wdBasket->getIterator();
        $this->assertEquals(1, $wdBasketIterator->count());

        $wdBasketIterator->seek(0);
        $currentItem = $wdBasketIterator->current()->mappedProperties();

        $this->assertEquals(Helper::translate('wd_shipping_title'), $currentItem['name']);
        $this->assertEquals(1, $currentItem['quantity']);
        $this->assertNull($currentItem['amount']['currency']);
        $this->assertEquals(0, $currentItem['amount']['value']);
        $this->assertEquals(Helper::translate('wd_shipping_title'), $currentItem['description']);
        $this->assertEquals(Helper::translate('wd_shipping_title'), $currentItem['article-number']);
        $this->assertEquals(0, $currentItem['tax-rate']);
    }

    public function testDiscountsAddedToTheBasket()
    {
        $discount = new stdClass();
        $discount->sDiscount = 'Title';
        $discount->dDiscount = 10;
        $discount->sOXID = 'Discount ID';

        $this->basketStub->expects($this->once())
            ->method('getDiscounts')
            ->willReturn(array($discount));

        $wdBasket = new TransactionBasket();
        BasketHelper::addDiscountsToBasket($wdBasket, $this->basketStub, "EUR");

        $wdBasketIterator = $wdBasket->getIterator();
        $this->assertEquals(1, $wdBasketIterator->count());

        $wdBasketIterator->seek(0);
        $currentItem = $wdBasketIterator->current()->mappedProperties();

        $this->assertEquals('Title', $currentItem['name']);
        $this->assertEquals(1, $currentItem['quantity']);
        $this->assertEquals('EUR', $currentItem['amount']['currency']);
        $this->assertEquals(-10.0, $currentItem['amount']['value']);
        $this->assertEquals('Title', $currentItem['description']);
        $this->assertEquals('Discount ID', $currentItem['article-number']);
        $this->assertNull($currentItem['tax-rate']);
    }
}
