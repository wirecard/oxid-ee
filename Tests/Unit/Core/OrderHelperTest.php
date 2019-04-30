<?php
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
use OxidEsales\Eshop\Core\Registry;
use Wirecard\Oxid\Extend\Model\Order;
use Wirecard\Oxid\Extend\Model\Payment;
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

    /**
     * @dataProvider testIsFinalizeOrderSuccessfulProvider
     */
    public function testIsFinalizeOrderSuccessful($iSuccess, $bExpected)
    {
        $this->assertEquals($bExpected, OrderHelper::isFinalizeOrderSuccessful($iSuccess));
    }

    public function testIsFinalizeOrderSuccessfulProvider()
    {
        return [
            'order state mailing error' => [Order::ORDER_STATE_MAILINGERROR, true],
            'order state okay' => [Order::ORDER_STATE_OK, true],
            'order state payment error' => [Order::ORDER_STATE_PAYMENTERROR, false],
        ];
    }

    /**
     *
     * @dataProvider testHandleFormResponseProvider
     */
    public function testHandleFormResponse($formPost)
    {
        $oSession = Registry::getSession();
        $oSession->setVariable('formPost', $formPost);
        $oPayment = oxNew(Payment::class);
        $oPayment->load('wdcreditcard');

        $oOrderStub = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        OrderHelper::handleFormResponse($oSession, $oPayment, $oOrderStub, 'formPost');
    }

    public function testHandleFormResponseProvider()
    {
        $successResponse = file_get_contents(__DIR__ . '/../../resources/success_response.xml');
        return [
            'empty post body' => [[]],
            'filled post body' => [["eppresponse" => "$successResponse"]],
        ];
    }
}
