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
                    ['oxid 2', 3, 'wdpaypal', 'transaction 5'],
                    ['oxid 3', 4, 'wdpaypal', 'transaction 8'],
                    ['oxid 4', 5, 'wdpaypal', 'transaction 12'],
                    ['oxid 5', 6, 'wdpaypal', 'transaction 17'],
                    ['oxid 6', 7, 'wdpaypal', 'transaction 19'],
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
                    ['transaction 5', 'oxid 2', 3, 'transaction 5', null, 'pay', 'debit', 'success', 33.8, 'EUR'],
                    ['transaction 6', 'oxid 2', 3, 'transaction 6', 'transaction 5', 'pay', 'refund-debit', 'closed', 20, 'EUR'],
                    ['transaction 7', 'oxid 2', 3, 'transaction 7', 'transaction 5', 'pay', 'refund-debit', 'closed', 13.7, 'EUR'],
                    ['transaction 8', 'oxid 3', 4, 'transaction 8', null, 'pay', 'debit', 'success', 33.8, 'EUR'],
                    ['transaction 9', 'oxid 3', 4, 'transaction 9', 'transaction 8', 'pay', 'refund-debit', 'closed', 20, 'EUR'],
                    ['transaction 10', 'oxid 3', 4, 'transaction 10', 'transaction 8', 'pay', 'refund-debit', 'closed', 13.7, 'EUR'],
                    ['transaction 11', 'oxid 3', 4, 'transaction 11', 'transaction 8', 'pay', 'refund-debit', 'closed', 0.1, 'EUR'],
                    ['transaction 12', 'oxid 4', 5, 'transaction 12', null, 'pay', 'debit', 'success', 9999.99, 'EUR'],
                    ['transaction 13', 'oxid 4', 5, 'transaction 13', 'transaction 12', 'pay', 'refund-debit', 'closed', 0.4, 'EUR'],
                    ['transaction 14', 'oxid 4', 5, 'transaction 14', 'transaction 12', 'pay', 'refund-debit', 'closed', 0.5, 'EUR'],
                    ['transaction 15', 'oxid 4', 5, 'transaction 15', 'transaction 12', 'pay', 'refund-debit', 'closed', 0.6, 'EUR'],
                    ['transaction 16', 'oxid 4', 5, 'transaction 16', 'transaction 12', 'pay', 'refund-debit', 'closed', 0.78, 'EUR'],
                    ['transaction 17', 'oxid 5', 6, 'transaction 17', null, 'pay', 'debit', 'success', 100, 'EUR'],
                    ['transaction 18', 'oxid 5', 6, 'transaction 18', 'transaction 17', 'pay', 'refund-debit', 'closed', 0.999, 'EUR'],
                    ['transaction 19', 'oxid 6', 7, 'transaction 19', null, 'pay', 'debit', 'success', 212000000.49, 'HUF'],
                    ['transaction 20', 'oxid 6', 7, 'transaction 20', 'transaction 19', 'pay', 'refund-debit', 'closed', 45555.65, 'HUF'],
                    ['transaction 21', 'oxid 6', 7, 'transaction 21', 'transaction 19', 'pay', 'refund-debit', 'closed', 97855.69, 'HUF'],
                    ['transaction 22', 'oxid 6', 7, 'transaction 22', 'transaction 19', 'pay', 'refund-debit', 'closed', 7855.28, 'HUF'],
                    ['transaction 23', 'oxid 6', 7, 'transaction 23', 'transaction 19', 'pay', 'refund-debit', 'closed', 85256.47, 'HUF'],
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
            'transaction 5' => ['transaction 5', 0.1],
            'transaction 8' => ['transaction 8', 0],
            'transaction 12' => ['transaction 12', 9997.71],
            'transaction 17' => ['transaction 17', 99.0],
            'transaction 19' => ['transaction 19', 211763477.4],
        ];
    }
}
