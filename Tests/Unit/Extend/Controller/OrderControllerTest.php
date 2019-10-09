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
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Price;
use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Extend\Controller\OrderController;
use Wirecard\Oxid\Extend\Model\Basket;

class OrderControllerTest extends \Wirecard\Test\WdUnitTestCase
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
        $this->_mockBasketGetPaymentId();

        $result = $this->_controller->init();
        $this->assertTrue($result);
    }

    public function testInitFromRedirect()
    {
        oxTestModules::addFunction(
            'oxUtils',
            'redirect',
            '{ return $aA[0]; }');

        $this->setRequestParameter('wdpayment', 'sessionToken');
        Registry::getSession()->setVariable('wdtoken', "sessionToken");

        $oUserStub = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_controller->setUser($oUserStub);
        $this->_mockBasketGetPaymentId();

        $result = $this->_controller->init();
        $this->assertContains('wdtoken=sessionToken', $result);
    }

    public function testInitFromRedirectFromForm()
    {
        oxTestModules::addFunction(
            'oxUtils',
            'redirect',
            '{ return $aA[0]; }');
        $this->setRequestParameter('wdpayment', 'sessionToken');
        $this->setRequestParameter('redirectFromForm', true);
        Registry::getSession()->setVariable('wdtoken', "sessionToken");

        $oUserStub = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_controller->setUser($oUserStub);
        $this->_mockBasketGetPaymentId();

        $result = $this->_controller->init();
        $this->assertContains('wdtoken=sessionToken', $result);
    }

    public function testExecuteWithPayError()
    {

        $this->_mockBasketGetPaymentId();

        $result = $this->_controller->execute();
        $this->assertEquals('payment?payerror=2', $result);
    }

    public function testExecuteWithOrderLoaded()
    {
        DatabaseProvider::getDb()->execute(
            "INSERT INTO oxorder(`oxid`) VALUES('oxid1');"
        );

        $this->_mockBasketGetPaymentId();

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
        $this->assertContains('/loader/paymentPage.js', $sLinkText);
    }

    public function testGetCreditCardFormRequestDataAjax()
    {
        oxTestModules::addFunction(
            'oxUtils',
            'showMessageAndExit',
            '{ return $aA[0]; }');

        $oBasketStub = $this->getMockBuilder(Basket::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPrice', 'getPaymentId'])
            ->getMock();

        $oBasketStub->method('getPaymentId')
            ->willReturn('wdcreditcard');

        $oPriceStub = $this->getMockBuilder(Price::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBruttoPrice'])
            ->getMock();

        $oPriceStub->method('getBruttoPrice')
            ->willReturn(100.9999);

        $oBasketStub->method('getPrice')
            ->willReturn($oPriceStub);

        $oUserStub = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['__get', 'getFieldData'])
            ->getMock();

        $oUserStub->method('getFieldData')->with('oxregister')->willReturn('2019-10-03');

        $oUserStub->method('__get')
            ->will(
                $this->returnCallback(
                    function ($sA) {
                        return new Field($sA);
                    })
            );

        Registry::getSession()->setBasket($oBasketStub);

        $this->_controller->setUser($oUserStub);
        $sResult = $this->_controller->getCreditCardFormRequestDataAjax();
        $this->assertNotNull($sResult);
        $oJson = json_decode(json_decode($sResult)->requestData);

        $this->assertEquals('oxuser__oxusername', $oJson->email);
        $this->assertEquals('oxuser__oxstreet oxuser__oxstreetnr', $oJson->street1);
        $this->assertEquals('creditcard', $oJson->payment_method);
    }

    private function _mockBasketGetPaymentId()
    {
        $oBasketStub = $this->getMockBuilder(Basket::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPaymentId'])
            ->getMock();

        $oBasketStub->method('getPaymentId')
            ->willReturn('wdpaypal');

        Registry::getSession()->setBasket($oBasketStub);
    }
}
