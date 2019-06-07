<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Core\Field;

use PHPUnit\Framework\MockObject\MockObject;

use Wirecard\Oxid\Controller\Admin\Transaction\TransactionTabPostProcessing;
use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\TransactionHandler;
use Wirecard\Oxid\Extend\Model\Payment;
use Wirecard\Oxid\Model\Transaction;

use Wirecard\PaymentSdk\BackendService;

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
                    ['oxid 3', 4, 'wdratepay-invoice', 'transaction 3'],
                ]
            ],
            [
                'table' => 'wdoxidee_ordertransactions',
                'columns' => ['oxid', 'orderid', 'ordernumber', 'transactionid', 'parenttransactionid', 'action', 'type', 'state', 'amount', 'currency', 'responsexml'],
                'rows' => [
                    ['transaction 1', 'oxid 1', 2, 'transaction 1', null, 'reserve', 'authorization', 'success', 100, 'EUR', $sEncodedXml],
                    ['transaction 2', 'oxid 2', 3, 'transaction 2', null, 'pay', 'debit', 'success', 100, 'EUR', $sEncodedXml],
                    ['transaction 3', 'oxid 3', 4, 'transaction 3', null, 'reserve', 'authorize', 'success', 100, 'EUR', $sEncodedXml],
                ]
            ],
        ];
    }

    /**
     * @dataProvider renderBasicProvider
     */
    public function testRenderBasic($sContainsKey)
    {
        $this->setRequestParameter('oxid', 'transaction 1');
        $this->_transactionTabPostProcessing = new TransactionTabPostProcessing();
        $this->_transactionTabPostProcessing->render();
        $this->assertArrayHasKey($sContainsKey, $this->_transactionTabPostProcessing->getViewData());
    }

    public function renderBasicProvider()
    {
        return [
            'contains actions' => ['actions'],
            'contains requestParameters' => ['requestParameters'],
            'contains alert' => ['message'],
            'contains currency' => ['currency'],
            'contains emptyText' => ['emptyText'],
        ];
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

    public function testRenderWithOrderItems()
    {
        $this->setRequestParameter('article-number', ["1", "2"]);
        $this->setRequestParameter('quantity', ["3", "4"]);
        $this->setRequestParameter('oxid', 'transaction 3');
        $this->_transactionTabPostProcessing = new TransactionTabPostProcessing();
        $this->_transactionTabPostProcessing->render();
        $aViewData = $this->_transactionTabPostProcessing->getViewData();

        $this->assertArrayHasKey('data', $aViewData);
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
     * @dataProvider invalidAmountInputProvider
     */
    public function testInvalidAmountInput($input)
    {
        $this->_oBackendServiceStub->method('retrieveBackendOperations')
            ->willReturn(['pay' => 'Pay']);

        $this->_oTransactionHandlerStub->method('processAction')
            ->willReturn(['status' => Transaction::STATE_SUCCESS]);

        $this->setRequestParameter('oxid', 'transaction 1');
        $this->setRequestParameter('0', 'pay');
        $this->setRequestParameter(TransactionTabPostProcessing::KEY_AMOUNT, $input);
        $this->setRequestParameter(TransactionTabPostProcessing::KEY_ACTION, 'pay');

        $oPayment = oxNew(Payment::class);
        $oPayment->load('wdpaypal');
        $oPayment->oxpayments__oxactive = new Field(1);
        $oPayment->save();

        $this->_transactionTabPostProcessing = new TransactionTabPostProcessing();
        $this->_transactionTabPostProcessing->setBackendService($this->_oBackendServiceStub);
        $this->_transactionTabPostProcessing->setTransactionHandler($this->_oTransactionHandlerStub);
        $this->_transactionTabPostProcessing->render();

        $aViewData = $this->_transactionTabPostProcessing->getViewData();

        $this->assertEquals('error', $aViewData['message']['type']);
        $this->assertLoggedException(\OxidEsales\Eshop\Core\Exception\StandardException::class);
    }

    public function invalidAmountInputProvider()
    {
        return [
            'text' => ['abcd'],
            'wrong format' => ['12,2565585.001'],
            'below zero' => ['-1'],
            'higher than transaction max amount' => ['10000000000'],
        ];
    }

    public function testAmountInput()
    {
        $this->_oBackendServiceStub->method('retrieveBackendOperations')
            ->willReturn(['pay' => 'Pay']);

        $this->_oTransactionHandlerStub->method('processAction')
            ->willReturn(['status' => Transaction::STATE_SUCCESS]);

        $this->setRequestParameter('oxid', 'transaction 1');
        $this->setRequestParameter('0', 'pay');
        $this->setRequestParameter(TransactionTabPostProcessing::KEY_AMOUNT, '100.00');
        $this->setRequestParameter(TransactionTabPostProcessing::KEY_ACTION, 'pay');

        $oPayment = oxNew(Payment::class);
        $oPayment->load('wdpaypal');
        $oPayment->oxpayments__oxactive = new Field(1);
        $oPayment->save();

        $this->_transactionTabPostProcessing = new TransactionTabPostProcessing();
        $this->_transactionTabPostProcessing->setBackendService($this->_oBackendServiceStub);
        $this->_transactionTabPostProcessing->setTransactionHandler($this->_oTransactionHandlerStub);
        $this->_transactionTabPostProcessing->render();

        $aViewData = $this->_transactionTabPostProcessing->getViewData();

        $this->assertEquals('success', $aViewData['message']['type']);
    }

    /**
     * @dataProvider requestActionProvider
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

    public function requestActionProvider()
    {
        $aSuccessResponseStub = ['status' => Transaction::STATE_SUCCESS];
        $aFailureResponseStub = ['status' => Transaction::STATE_ERROR];

        return [
            'success response' => [$aSuccessResponseStub],
            'failure response' => [$aFailureResponseStub],
        ];
    }
}
