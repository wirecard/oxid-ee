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
use OxidEsales\Eshop\Application\Model\Order as Order;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Price;

use Wirecard\Oxid\Core\BasketHelper;
use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\ThreedsHelper;

use Wirecard\PaymentSdk\Entity\Basket as TransactionBasket;


class ThreedsHelperTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testGetShippingAddressFirstUsed()
    {
        //use OxidEsales\Eshop\Application\Model\Order;

        // creating order
        $order = oxNew(Order::class);

        /** @var Order|PHPUnit_Framework_MockObject_MockObject $oOrderMock */
        $oOrderMock = $this->getMock(Order::class);
        ThreedsHelper::getShippingAddressFirstUsed($order);
    }

}
