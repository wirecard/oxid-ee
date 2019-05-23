<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Application\Model\OrderArticle;

use Wirecard\Oxid\Extend\Model\Basket;

class BasketTest extends \Wirecard\Test\WdUnitTestCase
{

    /**
     * @var Basket
     */
    private $_basket;

    protected function setUp()
    {
        $this->_basket = oxNew(Basket::class);
        parent::setUp();
    }

    public function testCreateTransactionBasket()
    {
        $oOrderArticleStub = $this->getMockBuilder(OrderArticle::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oOrderArticleStub->method('__get')
            ->willReturn(15);

        $oOrderArticleStub->method('isBundle')
            ->willReturn(false);

        $oOrderArticleStub->method('getId')
            ->willReturn('123456789');

        $this->_basket->addOrderArticleToBasket($oOrderArticleStub);

        $oTransactionBasket = $this->_basket->createTransactionBasket();
        $this->assertInstanceOf(\Wirecard\PaymentSdk\Entity\Basket::class, $oTransactionBasket);
    }
}
