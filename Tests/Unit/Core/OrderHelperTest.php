<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\UserPayment;
use Wirecard\Oxid\Extend\Model\Order;
use Wirecard\Test\WdUnitTestCase;

class OrderHelperTest extends WdUnitTestCase
{

    public function testCreateOrder()
    {
        $oBasketStub = $this->getMockBuilder(Basket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oUserStub = $this->getMockBuilder(UserPayment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oOrder = OrderHelper::createOrder($oBasketStub, $oUserStub);

        $this->assertInstanceOf(Order::class, $oOrder);
    }

    /**
     * @dataProvider testIsPaymentFinishedProvider
     */
    public function testIsPaymentFinished($sSessionToken, $sPaymentRedirect, $bExpected)
    {
        $this->assertEquals($bExpected, OrderHelper::isPaymentFinished($sSessionToken, $sPaymentRedirect));
    }

    public function testIsPaymentFinishedProvider()
    {
        return [
            'is finished' => ['test', 'test', true],
            'is not finished' => ['test', 'other', false],
        ];
    }
}
