<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Extend\Payment_Gateway;
use Wirecard\Oxid\Extend\Order;
use Wirecard\Oxid\Model\Paypal_Payment_Method;

class Payment_Gateway_Test extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var \Wirecard\Oxid\Extend\Order
     */
    private $oOrderMock;

    /**
     * @var Payment_Gateway
     */
    private $oPaymentGateway;

    protected function setUp()
    {
        parent::setUp();
        $this->oOrderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['isModulePaymentType', 'getPaymentType'])
            ->getMock();

        $this->oPaymentGateway = oxNew(Payment_Gateway::class);
    }

    //TODO cgrach: Check wrong behavior
//    public function testExecutePayment()
//    {
//        $this->oOrderMock->expects($this->once())
//            ->method('isModulePaymentType')
//            ->willReturn(true);
//
//        $this->oOrderMock->expects($this->once())
//            ->method('getPaymentType')
//            ->willReturn(Paypal_Payment_Method::getName(true));
//
//        $this->assertTrue($this->oPaymentGateway->executePayment(1.5, $this->oOrderMock));
//    }
}
