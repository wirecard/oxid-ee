<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use Wirecard\Oxid\Extend\Controller\OrderController;

class OrderControllerTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var OrderController
     */
    private $_controller;

    protected function setUp()
    {
        $this->_controller = oxNew(OrderController::class);

        parent::setUp();
    }

    public function testInit()
    {

        $result = $this->_controller->init();
        $this->assertTrue($result);
    }

    public function testInitFromRedirect()
    {
        oxTestModules::addFunction(
            'oxUtils',
            'redirect',
            '{ return $aA[0]; }');

        $_POST['wdpayment'] = "sessionToken";
        Registry::getSession()->setVariable('wdtoken', "sessionToken");

        $oUserStub = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_controller->setUser($oUserStub);

        $result = $this->_controller->init();
        $this->assertContains('wdtoken=sessionToken', $result);
    }

    public function testInitFromRedirectFromForm()
    {
        oxTestModules::addFunction(
            'oxUtils',
            'redirect',
            '{ return $aA[0]; }');

        $_POST['wdpayment'] = "sessionToken";
        $_POST['redirectFromForm'] = true;
        Registry::getSession()->setVariable('wdtoken', "sessionToken");

        $oUserStub = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_controller->setUser($oUserStub);

        $result = $this->_controller->init();
        $this->assertContains('wdtoken=sessionToken', $result);
    }

    public function testExecuteWithPayError()
    {
        $oBasketStub = $this->getMockBuilder(\Wirecard\Oxid\Extend\Model\Basket::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPaymentId'])
            ->getMock();

        $oBasketStub->method('getPaymentId')
            ->willReturn('wdpaypal');

        Registry::getSession()->setBasket($oBasketStub);

        $result = $this->_controller->execute();
        $this->assertEquals('payment?payerror=2', $result);
    }

    public function testExecuteWithOrderLoaded()
    {
        DatabaseProvider::getDb()->execute(
            "INSERT INTO oxorder(`oxid`) VALUES('oxid1');"
        );

        $oBasketStub = $this->getMockBuilder(\Wirecard\Oxid\Extend\Model\Basket::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPaymentId'])
            ->getMock();

        $oBasketStub->method('getPaymentId')
            ->willReturn('wdpaypal');

        Registry::getSession()->setBasket($oBasketStub);
        Registry::getSession()->setVariable('sess_challenge', 'oxid1');

        $result = $this->_controller->execute();
        $this->assertEquals('payment?payerror=2', $result);
    }

    public function testGetCCRequestDataAjaxLink()
    {
        $sLinkText = $this->_controller->getCCRequestDataAjaxLink();
        $this->assertContains('cl=order&fnc=getCreditCardFormRequestDataAjax', $sLinkText);
    }

    public function testGetPaymentPageLoaderScriptUrl()
    {
        $sLinkText = $this->_controller->getPaymentPageLoaderScriptUrl();
        $this->assertContains('/engine/hpp/paymentPageLoader.js', $sLinkText);
    }
}
