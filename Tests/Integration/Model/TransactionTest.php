<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\Transaction;

use OxidEsales\Eshop\Application\Model\Order;

class TransactionTest extends Wirecard\Test\WdUnitTestCase
{
    protected function dbData()
    {
        return [
            [
                'table' => 'wdoxidee_ordertransactions',
                'columns' => [
                    'oxid',
                    'transactionid',
                    'parenttransactionid',
                    'responsexml',
                ],
                'rows' => [
                    ['1', '1', null, base64_encode('<?xml version="1.0" encoding="UTF-8"?>')],
                    ['2', '1.1', '1', ''],
                    ['3', '1.2', '1', ''],
                    ['4', '2', null, ''],
                    ['5', '3', null, ''],
                ],
            ],
        ];
    }

    public function testLoadWithTransactionId()
    {
        $oTransaction = oxNew(Transaction::class);

        $this->assertTrue($oTransaction->loadWithTransactionId('1'));
    }

    /**
     * @dataProvider getChildTransactionsProvider
     */
    public function testGetChildTransactions($input, $expected)
    {
        $oTransaction = oxNew(Transaction::class);
        $oTransaction->load($input);

        $this->assertCount($expected, $oTransaction->getChildTransactions());
    }

    public function getChildTransactionsProvider()
    {
        return [
            'transaction with children' => ['1', 2],
            'transaction without children' => ['4', 0],
            'child transaction without children' => ['2', 0],
        ];
    }

    public function testGetTransactionOrder()
    {
        $oTransaction = oxNew(Transaction::class);

        $this->assertInstanceOf(Order::class, $oTransaction->getTransactionOrder());
    }

    public function testGetResponseXML()
    {
        $oTransaction = oxNew(Transaction::class);
        $oTransaction->load('1');

        $this->assertEquals($oTransaction->getResponseXML(), '<?xml version="1.0" encoding="UTF-8"?>');
    }

    public function testGetTranslatedTransactionTypes()
    {
        $oTransaction = oxNew(Transaction::class);
        $aTransactionTypes = $oTransaction->getTranslatedTransactionTypes();
        $this->assertCount(20, $aTransactionTypes);
    }

    public function testGetTranslatedStates()
    {
        $oTransaction = oxNew(Transaction::class);
        $aTransactionTypes = $oTransaction->getTranslatedStates();
        $this->assertCount(4, $aTransactionTypes);
    }
}
