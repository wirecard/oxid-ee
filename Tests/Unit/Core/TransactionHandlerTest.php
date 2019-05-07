<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use PHPUnit\Framework\MockObject\MockObject;

use Wirecard\Oxid\Model\Transaction;
use Wirecard\Oxid\Core\TransactionHandler;
use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Entity\Status;
use Wirecard\PaymentSdk\Entity\StatusCollection;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;

class TransactionHandlerTest extends Wirecard\Test\WdUnitTestCase
{
    /**
     * @var TransactionHandler
     */
    private $_oTransactionHandler;

    /**
     * @var BackendService|MockObject
     */
    private $_oBackendServiceStub;

    protected function setUp()
    {
        $this->_oBackendServiceStub = $this->getMockBuilder(BackendService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_oTransactionHandler = new TransactionHandler($this->_oBackendServiceStub);

        parent::setUp();
    }

    protected function dbData()
    {
        return [
            [
                'table' => 'oxorder',
                'columns' => ['oxid', 'oxordernr', 'oxpaymenttype', 'wdoxidee_transactionid'],
                'rows' => [
                    ['oxid 1', 2, 'wdpaypal', 'transaction 1'],
                ]
            ],
            [
                'table' => 'wdoxidee_ordertransactions',
                'columns' => ['oxid', 'orderid', 'ordernumber', 'transactionid', 'parenttransactionid', 'action', 'type', 'state', 'amount', 'currency'],
                'rows' => [
                    ['transaction 1', 'oxid 1', 2, 'transaction 1', null, 'reserve', 'authorization', 'success', 100, 'EUR'],
                    ['transaction 2', 'oxid 1', 2, 'transaction 2', 'transaction 1', 'reserve', 'capture-authorization', 'success', 40, 'EUR'],
                    ['transaction 3', 'oxid 1', 2, 'transaction 3', 'transaction 1', 'reserve', 'capture-authorization', 'closed', 40, 'EUR'],
                    ['transaction 4', 'oxid 1', 2, 'transaction 4', 'transaction 3', 'reserve', 'refund-capture', 'closed', 40, 'EUR'],
                ]
            ],
        ];
    }

    protected function failOnLoggedExceptions()
    {
        $this->exceptionLogHelper->clearExceptionLogFile();
    }

    /**
     * @dataProvider testProcessActionProvider
     */
    public function testProcessAction($oResponseStub)
    {
        $this->_oBackendServiceStub->method('process')
            ->willReturn($oResponseStub);

        $this->_oBackendServiceStub->method('isFinal')
            ->willReturn(false);

        $oParentTransaction = oxNew(Transaction::class);
        $oParentTransaction->loadWithTransactionId('transaction 1');

        $oResponse = $this->_oTransactionHandler->processAction($oParentTransaction, 'capture', 20);

        $this->assertArrayHasKey('status', $oResponse);

        if ($oResponseStub instanceof FailureResponse) {
            $this->assertArrayHasKey('message', $oResponse);
        }
    }

    public function testProcessActionProvider()
    {
        $successXml = simplexml_load_string(file_get_contents(dirname(__FILE__) . '/../../resources/success_response_transaction_handler.xml'));
        $oSuccessResponse = new SuccessResponse($successXml);

        $oFailureResponseStub = $this->getMockBuilder(FailureResponse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statusCollection = new StatusCollection();
        $statusCollection->add(new Status("123", "description", "minor"));

        $oFailureResponseStub->method('getStatusCollection')
            ->willReturn($statusCollection);

        $oFailureResponseStub->method('getParentTransactionId')
            ->willReturn('transaction1');

        return [
            'success response' => [$oSuccessResponse],
            'failure response' => [$oFailureResponseStub],
        ];
    }

    /**
     * @dataProvider testGetTransactionMaxAmountProvider
     */
    public function testGetTransactionMaxAmount($sTransactionId, $fExpectedAmount)
    {
        $fAmount = $this->_oTransactionHandler->getTransactionMaxAmount($sTransactionId);
        $this->assertEquals($fAmount, $fExpectedAmount);
    }

    public function testGetTransactionMaxAmountProvider()
    {
        return [
            'transaction 1' => ['transaction 1', 20],
            'transaction 2' => ['transaction 2', 40],
            'transaction 3' => ['transaction 3', 0],
            'transaction 4' => ['transaction 4', 40],
        ];
    }
}
