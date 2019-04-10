<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\TransactionList;

use OxidEsales\Eshop\Core\DatabaseProvider;

class TransactionListTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $oDb = DatabaseProvider::getDb();

        foreach ($this->getTestTransactionData() as $row) {
            $oDb->execute("INSERT INTO `wdoxidee_ordertransactions`(`oxid`, `orderid`, `transactionid`,
                `parenttransactionid`, `date`) VALUES (?, ?, ?, ?, ?)", $row);
        }
    }

    protected function tearDown()
    {
        parent::tearDown();

        $oDb = DatabaseProvider::getDb();
        $oDb->execute('TRUNCATE TABLE `wdoxidee_ordertransactions`');
    }

    private function getTestTransactionData()
    {
        return [
            ['1', '1', '1', null, '2000-01-01 00:00:00'],
            ['2', '1', '1.1', '1', '2000-01-02 00:01:00'],
            ['3', '1', '1.2', '1', '2000-01-02 00:00:00'],
            ['4', '1', '1.2.1', '1.2', '2000-01-03 00:00:00'],
            ['5', '2', '2', null, '1999-01-01 00:00:00'],
        ];
    }

    /**
     * @dataProvider testGetListByConditionsProvider
     */
    public function testGetListByConditions($input, $expected)
    {
        $oTransactionList = oxNew(TransactionList::class);

        $this->assertCount($expected, $oTransactionList->getListByConditions($input)->getArray());
    }

    public function testGetListByConditionsProvider()
    {
        return [
            'orderid' => [
                ['orderid' => '1'],
                4,
            ],
            'parenttransactionid' => [
                ['parenttransactionid' => '1'],
                2,
            ],
            'multiple' => [
                [
                    'parenttransactionid' => '1',
                    'oxid' => '2',
                ],
                1,
            ],
        ];
    }

    /**
     * @dataProvider testGetListByConditionsOrderProvider
     */
    public function testGetListByConditionsOrder($input, $expected)
    {
        $oTransactionList = oxNew(TransactionList::class);
        $resultIds = array_keys($oTransactionList->getListByConditions([], $input)->getArray());

        $this->assertEquals($resultIds, $expected);
    }

    public function testGetListByConditionsOrderProvider()
    {
        return [
            'oxid' => [
                'oxid',
                ['1', '2', '3', '4', '5'],
            ],
            'date' => [
                'date',
                ['5', '1', '3', '2', '4'],
            ],
            'parenttransactionid' => [
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
