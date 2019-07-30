<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use PHPUnit\Framework\MockObject\MockObject;

use Wirecard\Oxid\Controller\NotifyHandler;
use Wirecard\Oxid\Model\PaymentMethod\CreditCardPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\PaypalPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\SofortPaymentMethod;

use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Entity\Status;
use Wirecard\PaymentSdk\Entity\StatusCollection;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Response\SuccessResponse;

class NotifyHandlerTest extends \Wirecard\Test\WdUnitTestCase
{

    /**
     * @var NotifyHandler
     */
    private $_oNotifyHandler;

    /**
     * @var BackendService|MockObject
     */
    private $_oBackendServiceStub;

    protected function setUp()
    {
        $this->_oBackendServiceStub = $this->getMockBuilder(BackendService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_oNotifyHandler = oxNew(NotifyHandler::class);
        $this->_oNotifyHandler->setBackendService($this->_oBackendServiceStub);

        parent::setUp();
    }

    protected function dbData()
    {
        return [
            [
                'table' => 'oxorder',
                'columns' => ['oxid', 'wdoxidee_transactionid'],
                'rows' => [
                    ['oxid 1', 'transactionId'],
                ]
            ],
            [
                'table' => 'wdoxidee_ordertransactions',
                'columns' => ['oxid', 'type'],
                'rows' => [
                    ['transactionId', 'pending']
                ]
            ],
        ];
    }

    protected function failOnLoggedExceptions()
    {
        $this->exceptionLogHelper->clearExceptionLogFile();
        // Do not fail - we log error on failure response which is okay
    }

    /**
     * @dataProvider handleRequestProvider
     *
     * @param Response|MockObject $oResponseStub
     */
    public function testHandleRequest($oResponseStub)
    {
        $this->_oBackendServiceStub->method('handleNotification')
            ->willReturn($oResponseStub);

        $_GET['pmt'] = 'wdpaypal';

        try {
            $this->_oNotifyHandler->handleRequest();
        } catch (\OxidEsales\Eshop\Core\Exception\StandardException $exception) {
            $this->fail("Exception thrown: " . get_class($exception));
        }
    }

    public function handleRequestProvider()
    {
        $oSuccessResponseStub = $this->getMockBuilder(SuccessResponse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oSuccessResponseStub->method('getParentTransactionId')
            ->willReturn('transactionId');

        $oSuccessWithoutTransactionStub = $this->getMockBuilder(SuccessResponse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oSuccessWithoutTransactionStub->method('getTransactionId')
            ->willReturn('noExistingTransactionId');

        $oFailureResponseStub = $this->getMockBuilder(FailureResponse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statusCollection = new StatusCollection();
        $statusCollection->add(new Status("123", "description", "minor"));

        $oFailureResponseStub->method('getStatusCollection')
            ->willReturn($statusCollection);

        $oFailureResponseStub->method('getData')
            ->willReturn(['parent-transaction-id' => "parent Transaction Id"]);

        return [
            'success response' => [$oSuccessResponseStub],
            'success response without existing transaction' => [$oSuccessWithoutTransactionStub],
            'failure response' => [$oFailureResponseStub],
        ];
    }

    /**
     * @dataProvider getNotificationUrlProvider
     */
    public function testGetNotificationUrl($oPaymentMethod, $oPaymentId) {
        $result = NotifyHandler::getNotificationUrl($oPaymentMethod);

        $this->assertContains("cl=wcpg_notifyhandler&fnc=handleRequest&pmt=$oPaymentId", $result);
    }

    public function getNotificationUrlProvider() {
        return [
            "Paypal notification Url" => [new PaypalPaymentMethod(), 'wdpaypal'],
            "Credit card notification Url" => [new CreditCardPaymentMethod(), 'wdcreditcard'],
            "Sofort. notification Url" => [new SofortPaymentMethod(), 'wdsofortbanking'],
        ];
    }
}
