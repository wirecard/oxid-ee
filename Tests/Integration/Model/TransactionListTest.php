<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\TransactionList;

class TransactionListTest extends Wirecard\Test\WdUnitTestCase
{
    protected function dbData()
    {
        return [
            [
                'table' => 'wdoxidee_ordertransactions',
                'columns' => [
                    'oxid',
                    'orderid',
                    'transactionid',
                    'parenttransactionid',
                    'date',
                ],
                'rows' => [
                    ['1', '1', '1', null, '2000-01-01 00:00:00'],
                    ['2', '1', '1.1', '1', '2000-01-02 00:01:00'],
                    ['3', '1', '1.2', '1', '2000-01-02 00:00:00'],
                    ['4', '1', '1.2.1', '1.2', '2000-01-03 00:00:00'],
                    ['5', '2', '2', null, '1999-01-01 00:00:00'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getListByConditionsProvider
     */
    public function testGetListByConditions($input, $expected)
    {
        $oTransactionList = oxNew(TransactionList::class);

        $this->assertCount($expected, $oTransactionList->getListByConditions($input)->getArray());
    }

    public function getListByConditionsProvider()
    {
        return [
            'get by orderid' => [
                ['orderid' => '1'],
                4,
            ],
            'get by parenttransactionid' => [
                ['parenttransactionid' => '1'],
                2,
            ],
            'get by multiple columns' => [
                [
                    'parenttransactionid' => '1',
                    'oxid' => '2',
                ],
                1,
            ],
        ];
    }

    /**
     * @dataProvider getListByConditionsOrderProvider
     */
    public function testGetListByConditionsOrder($input, $expected)
    {
        $oTransactionList = oxNew(TransactionList::class);
        $resultIds = array_keys($oTransactionList->getListByConditions([], $input)->getArray());

        $this->assertEquals($resultIds, $expected);
    }

    public function getListByConditionsOrderProvider()
    {
        return [
            'order by oxid' => [
                'oxid',
                ['1', '2', '3', '4', '5'],
            ],
            'order by date' => [
                'date',
                ['5', '1', '3', '2', '4'],
            ],
            'order by parenttransactionid' => [
                'parenttransactionid',
                ['1', '5', '2', '3', '4'],
            ],
        ];
    }

    public function testGetNestedArray()
    {
        $oTransactionList = oxNew(TransactionList::class);

        $this->assertCount(2, $oTransactionList->getList()->getNestedArray());
    }
}
