<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 *
 */

use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\UserPayment;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Field;

use PHPUnit\Framework\MockObject\MockObject;

use Wirecard\Oxid\Extend\Core\Email;
use Wirecard\Oxid\Extend\Model\Basket;

class EmailTest extends OxidEsales\TestingLibrary\UnitTestCase
{

    /**
     * @var Email|MockObject
     */
    private $_oEmail;

    protected function setUp()
    {
        $this->_oEmail = oxNew(Email::class);

        parent::setUp();
    }

    /**
     * @dataProvider testSendSupportEmailProvider
     */
    public function testSendSupportEmail($aEmailData)
    {
        $oSmartyMock = $this->getMockBuilder(Smarty::class)
            ->setMethods(["fetch", "assign"])
            ->getMock();

        $oSmartyMock->expects($this->any())
            ->method("fetch")
            ->willReturn(true);

        $oEmail = $this->getMockBuilder(Email::class)
            ->setMethods(["_sendMail", "_getSmarty", "send"])
            ->getMock();
        $oEmail->expects($this->any())->method("_getSmarty")
            ->willReturn($oSmartyMock);

        $oEmail->sendSupportEmail($aEmailData);

        $aViewData = $oEmail->getViewData();
        $sSubject = $oEmail->getSubject();
        $sFrom = $oEmail->getFrom();
        $sReplyTo = $oEmail->getReplyTo()[0][0];

        $this->assertEquals($aViewData['emailData']['from'], $aEmailData['from']);
        $this->assertEquals($sSubject, $aEmailData['subject']);
        $this->assertEquals($sFrom, $aEmailData['from']);

        if ($aEmailData['replyTo']) {
            $this->assertEquals($sReplyTo, $aEmailData['replyTo']);
        } else {
            $this->assertEquals($sReplyTo, $aEmailData['from']);
        }

    }

    public function testSendSupportEmailProvider()
    {
        $aEmailDataWithoutReplyTo = [
            'body' => 'body test',
            'from' => 'test@from.test',
            'modules' => [],
            'module' => null,
            'shopVersion' => 'test version',
            'shopEdition' => 'test edition',
            'phpVersion' => 'test php version',
            'system' => 'test system',
            'subject' => 'test subject',
            'recipient' => 'test@recipient.test',
            'payments' => [],
        ];

        $aEmailDataComplete = $aEmailDataWithoutReplyTo;
        $aEmailDataComplete['replyTo'] = 'test@reply.test';

        return [
            'withoutReplyTo' => [$aEmailDataWithoutReplyTo],
            'complete' => [$aEmailDataComplete]
        ];
    }

    public function testSendOrderEmailToUser()
    {
        $oOrderStub = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['isCustomPaymentMethod', 'getOrderUser', 'getBasket', 'getPayment', '__get'])
            ->getMock();

        $oOrderStub->method('__get')
            ->will(
                $this->returnCallback(
                    function ($sA) {
                        return new Field($sA);
                    })
            );

        $oOrderStub->method('isCustomPaymentMethod')
            ->willReturn(true);

        $oBasket = $this->getMockBuilder(Basket::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBasketArticles'])
            ->getMock();

        $oOrderStub->method('getBasket')
            ->willReturn($oBasket);

        $oPaymentStub = $this->getMockBuilder(UserPayment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oPaymentStub->method('__get')
            ->with('oxuserpayments__oxpaymentsid')
            ->willReturn(new Field('oxempty'));

        $oOrderStub->method('getPayment')
            ->willReturn($oPaymentStub);

        $oUserStub = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['__get'])
            ->getMock();

        $oUserStub->method('__get')
            ->will(
                $this->returnCallback(
                    function ($sA) {
                        return new Field($sA);
                    })
            );

        $oOrderStub->method('getOrderUser')
            ->willReturn($oUserStub);

        $oArticleStub = $this->getMockBuilder(Article::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oBasket->method('getBasketArticles')
            ->willReturn([$oArticleStub]);

        $sent = $this->_oEmail->sendOrderEmailToUser($oOrderStub, "my subject");

        //email not send because of the test setup but finished without errors.
        $this->assertFalse($sent);
    }

    public function testSendOrderEmailToOwner()
    {
        $oOrderStub = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['isCustomPaymentMethod', 'getOrderUser', 'getBasket', 'getPayment', '__get'])
            ->getMock();

        $oOrderStub->method('__get')
            ->will(
                $this->returnCallback(
                    function ($sA) {
                        return new Field($sA);
                    })
            );

        $oArticleStub = $this->getMockBuilder(Article::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oBasket = $this->getMockBuilder(Basket::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBasketArticles'])
            ->getMock();

        $oBasket->method('getBasketArticles')
            ->willReturn([$oArticleStub]);

        $oOrderStub->method('getBasket')
            ->willReturn($oBasket);

        $oPaymentStub = $this->getMockBuilder(UserPayment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oPaymentStub->method('__get')
            ->with('oxuserpayments__oxpaymentsid')
            ->willReturn(new Field('oxempty'));

        $oOrderStub->method('getPayment')
            ->willReturn($oPaymentStub);

        $oUserStub = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['__get'])
            ->getMock();

        $oUserStub->method('__get')
            ->will(
                $this->returnCallback(
                    function ($sA) {
                        return new Field($sA);
                    })
            );

        $oOrderStub->method('getOrderUser')
            ->willReturn($oUserStub);

        $sent = $this->_oEmail->sendOrderEmailToOwner($oOrderStub, "Subject");
        //email not send because of the test setup but finished without errors.
        $this->assertFalse($sent);
        $this->assertLoggedException(StandardException::class, 'Could not instantiate mail function.');
    }
}
