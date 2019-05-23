<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Core\Field;

use Wirecard\PaymentSdk\BackendService;

use Wirecard\Oxid\Controller\Admin\Transaction\TransactionTabPostProcessing;
use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\TransactionHandler;
use Wirecard\Oxid\Extend\Model\Payment;
use Wirecard\Oxid\Model\Transaction;

class TransactionTabPostProcessingTest extends Wirecard\Test\WdUnitTestCase
{
    /**
     * @var TransactionTabPostProcessing
     */
    private $_transactionTabPostProcessing;

    /**
     * @var BackendService|MockObject
     */
    private $_oBackendServiceStub;

    /**
     * @var TransactionHandler|MockObject
     */
    private $_oTransactionHandlerStub;

    protected function setUp()
    {
        $this->_oBackendServiceStub = $this->getMockBuilder(BackendService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_oTransactionHandlerStub = $this->getMockBuilder(TransactionHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['processAction'])
            ->getMock();

        parent::setUp();
    }

    protected function dbData()
    {
        $sEncodedXml = base64_encode(file_get_contents(dirname(__FILE__) . '/../../../../resources/success_response_transaction_handler.xml'));

        return [
            [
                'table' => 'oxorder',
                'columns' => ['oxid', 'oxordernr', 'oxpaymenttype', 'wdoxidee_transactionid'],
                'rows' => [
                    ['oxid 1', 2, 'wdpaypal', 'transaction 1'],
                    ['oxid 2', 3, 'wdsofortbanking', 'transaction 2'],
                ]
            ],
            [
                'table' => 'wdoxidee_ordertransactions',
                'columns' => ['oxid', 'orderid', 'ordernumber', 'transactionid', 'parenttransactionid', 'action', 'type', 'state', 'amount', 'currency', 'responsexml'],
                'rows' => [
                    ['transaction 1', 'oxid 1', 2, 'transaction 1', null, 'reserve', 'authorization', 'success', 100, 'EUR', $sEncodedXml],
                    ['transaction 2', 'oxid 2', 3, 'transaction 2', null, 'pay', 'debit', 'success', 100, 'EUR', $sEncodedXml],
                ]
            ],
        ];
    }

    public function testRenderBasic()
    {
        $_GET['oxid'] = 'transaction 1';
        $this->_transactionTabPostProcessing = new TransactionTabPostProcessing();
        $this->_transactionTabPostProcessing->render();
        $this->assertArrayHasKey('actions', $this->_transactionTabPostProcessing->getViewData());
        $this->assertArrayHasKey('requestParameters', $this->_transactionTabPostProcessing->getViewData());
        $this->assertArrayHasKey('message', $this->_transactionTabPostProcessing->getViewData());
        $this->assertArrayHasKey('currency', $this->_transactionTabPostProcessing->getViewData());
        $this->assertArrayHasKey('emptyText', $this->_transactionTabPostProcessing->getViewData());
    }

    public function testRenderSepaCredit()
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->load('wdsepacredit');
        $oPayment->oxpayments__oxactive = new Field(0);
        $oPayment->save();
        $this->_oBackendServiceStub->method('retrieveBackendOperations')->willReturn(['testkey' => 'testvalue']);

        $_GET['oxid'] = 'transaction 2';
        $this->_transactionTabPostProcessing = new TransactionTabPostProcessing();
        $this->_transactionTabPostProcessing->setBackendService($this->_oBackendServiceStub);
        $this->_transactionTabPostProcessing->render();

        $this->assertArrayHasKey('actions', $this->_transactionTabPostProcessing->getViewData());
        $this->assertEmpty($this->_transactionTabPostProcessing->getViewData()['actions']);
    }

    public function testDefaultTransactionAmount()
    {
        $this->_oBackendServiceStub->method('retrieveBackendOperations')
            ->willReturn(['pay' => 'Pay']);

        $_GET['oxid'] = 'transaction 1';

        $this->_transactionTabPostProcessing = new TransactionTabPostProcessing();
        $this->_transactionTabPostProcessing->setBackendService($this->_oBackendServiceStub);
        $this->_transactionTabPostProcessing->render();

        $aViewData = $this->_transactionTabPostProcessing->getViewData();
        $this->assertArrayHasKey('requestParameters', $aViewData);
        $this->assertArrayHasKey('amount', $aViewData['requestParameters']);

        $this->assertEquals($aViewData['requestParameters']['amount'], '100');
    }

    /**
     * @dataProvider testInvalidAmountInputProvider
     */
    public function testAmountInput($input, $expected)
    {
        $this->_oBackendServiceStub->method('retrieveBackendOperations')
            ->willReturn(['pay' => 'Pay']);

        $this->_oTransactionHandlerStub->method('processAction')
            ->willReturn(['status' => Transaction::STATE_SUCCESS]);

        $_GET['oxid'] = 'transaction 1';
        $_GET['0'] = 'pay';
        $_GET[TransactionTabPostProcessing::KEY_AMOUNT] = $input;

        $this->_transactionTabPostProcessing = new TransactionTabPostProcessing();
        $this->_transactionTabPostProcessing->setBackendService($this->_oBackendServiceStub);
        $this->_transactionTabPostProcessing->setTransactionHandler($this->_oTransactionHandlerStub);
        $this->_transactionTabPostProcessing->render();

        $aViewData = $this->_transactionTabPostProcessing->getViewData();
        $this->assertArrayHasKey('message', $aViewData);

        $this->assertArrayHasKey('type', $aViewData['message']);
        $this->assertEquals($aViewData['message']['type'], $expected);
    }

    public function testInvalidAmountInputProvider()
    {
        return [
            'text' => ['abcd', 'error'],
            'wrong format' => ['12,2565585.001', 'error'],
            'below zero' => ['-1', 'error'],
            'higher than transaction max amount' => ['10000000000', 'error'],
            'ok' => ['100.00', 'success'],
        ];
    }

    /**
     * @dataProvider testRequestActionProvider
     *
     * @param array $aResponseStub
     */
    public function testRequestAction($aResponseStub)
    {
        $this->_oBackendServiceStub->method('retrieveBackendOperations')
            ->willReturn(['pay' => 'Pay']);

        $this->_oTransactionHandlerStub->method('processAction')
            ->willReturn($aResponseStub);

        $this->_oTransactionHandlerStub->method('getTransactionMaxAmount')
            ->willReturn(100);

        $_GET['oxid'] = 'transaction 1';
        $_GET['0'] = 'pay';
        $_GET[TransactionTabPostProcessing::KEY_AMOUNT] = '100.0';

        $this->_transactionTabPostProcessing = new TransactionTabPostProcessing();
        $this->_transactionTabPostProcessing->setBackendService($this->_oBackendServiceStub);
        $this->_transactionTabPostProcessing->setTransactionHandler($this->_oTransactionHandlerStub);
        $this->_transactionTabPostProcessing->render();

        $aViewData = $this->_transactionTabPostProcessing->getViewData();


        if ($aResponseStub['status'] === Transaction::STATE_SUCCESS) {
            $this->assertEquals($aViewData['message']['message'], Helper::translate('wd_text_generic_success'));
        }

        if ($aResponseStub['status'] === Transaction::STATE_ERROR) {
            $this->assertNotEquals($aViewData['message']['message'], Helper::translate('wd_text_generic_success'));
        }
    }

    public function testRequestActionProvider()
    {
        $aSuccessResponseStub = ['status' => Transaction::STATE_SUCCESS];
        $aFailureResponseStub = ['status' => Transaction::STATE_ERROR];

        return [
            'success response' => [$aSuccessResponseStub],
            'failure response' => [$aFailureResponseStub],
        ];
    }
}
