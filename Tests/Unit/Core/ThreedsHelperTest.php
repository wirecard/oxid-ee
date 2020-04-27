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

use Wirecard\Oxid\Core\ThreedsHelper;

class ThreedsHelperTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    public function testHasDownloadableItems()
    {
        /** @var Basket|PHPUnit_Framework_MockObject_MockObject $oBasket */
        $oBasket = $this->getMockBuilder(Basket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oArticle = $this->getMockBuilder(Article::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oArticle->method('isDownloadable')->willReturn(false);

        $oBasket->method('getBasketArticles')->willReturn([
            $oArticle,
        ]);

        $this->assertFalse(ThreedsHelper::hasDownloadableItems($oBasket));
    }

    public function testHasDownloadableItemsWithDownloadable()
    {
        /** @var Basket|PHPUnit_Framework_MockObject_MockObject $oBasket */
        $oBasket = $this->getMockBuilder(Basket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oArticle = $this->getMockBuilder(Article::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oArticle->method('isDownloadable')->willReturn(true);

        $oBasket->method('getBasketArticles')->willReturn([
            $oArticle,
        ]);

        $this->assertTrue(ThreedsHelper::hasDownloadableItems($oBasket));
    }
}
