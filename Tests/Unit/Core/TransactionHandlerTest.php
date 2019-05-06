<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Core\TransactionHandler;

class TransactionHandlerTest extends \Wirecard\Test\WdUnitTestCase
{
    /**
     * @var TransactionHandler
     */
    private $_oTransactionHandler;

    protected function setUp()
    {
        $this->_oTransactionHandler = oxNew(TransactionHandler::class);

        parent::setUp();
    }

    protected function dbData()
    {
        return [
            [
                'table' => 'oxorder',
                'columns' => ['oxid', 'oxordernr', 'wdoxidee_transactionid'],
                'rows' => [
                    ['oxid 1', 2, 'transaction 1'],
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

    public function testProcessAction()
    {
        $this->assertTrue(true);
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
