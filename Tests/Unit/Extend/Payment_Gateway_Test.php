<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
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
            ->setMethods(['isWirecardPaymentType', 'getPaymentType'])
            ->getMock();

        $this->oPaymentGateway = oxNew(Payment_Gateway::class);
    }

    //TODO cgrach: Check wrong behavior
//    public function testExecutePayment()
//    {
//        $this->oOrderMock->expects($this->once())
//            ->method('isWirecardPaymentType')
//            ->willReturn(true);
//
//        $this->oOrderMock->expects($this->once())
//            ->method('getPaymentType')
//            ->willReturn(Paypal_Payment_Method::NAME);
//
//        $this->assertTrue($this->oPaymentGateway->executePayment(1.5, $this->oOrderMock));
//    }
}
