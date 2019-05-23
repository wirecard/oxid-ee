<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Core\Registry;

use PHPUnit\Framework\MockObject\MockObject;

use Wirecard\Oxid\Extend\Controller\ThankYouController;

class ThankYouControllerTest extends \Wirecard\Test\WdUnitTestCase
{
    /**
     * @var ThankYouController|MockObject
     */
    private $_thankYouController;

    protected function setUp()
    {
        $this->_thankYouController = oxNew(ThankYouController::class);
        parent::setUp();
    }

    protected function dbData()
    {
        return [
            [
                'table' => 'oxorder',
                'columns' => ['oxid', 'oxordernr'],
                'rows' => [
                    ['oxid1', '1'],
                ],
            ],
        ];
    }

    public function testInit()
    {
        $oBasketStub = $this->getMockBuilder(\Wirecard\Oxid\Extend\Model\Basket::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrderId'])
            ->getMock();

        $oBasketStub->method('getOrderId')
            ->willReturn('oxid1');

        Registry::getSession()->setBasket($oBasketStub);

        $this->_thankYouController->init();

        $this->assertNull(\OxidEsales\Eshop\Core\Registry::getSession()->getVariable('wdtoken'));
        $this->assertArrayHasKey('sendPendingEmailsSettings', $this->_thankYouController->getViewData());
    }
}
