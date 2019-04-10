<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\Transaction;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Application\Model\Order;

class TransactionTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $oDb = DatabaseProvider::getDb();

        foreach ($this->getTestTransactionData() as $row) {
            $oDb->execute("INSERT INTO `wdoxidee_ordertransactions`(`oxid`, `transactionid`,
                `parenttransactionid`, `responsexml`) VALUES (?, ?, ?, ?)", $row);
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
            ['1', '1', null, base64_encode('<?xml version="1.0" encoding="UTF-8"?>')],
            ['2', '1.1', '1', ''],
            ['3', '1.2', '1', ''],
            ['4', '2', null, ''],
            ['5', '3', null, ''],
        ];
    }

    public function testLoadWithTransactionId()
    {
        $oTransaction = oxNew(Transaction::class);

        $this->assertTrue($oTransaction->loadWithTransactionId('1'));
    }

    /**
     * @dataProvider testGetChildTransactionsProvider
     */
    public function testGetChildTransactions($input, $expected)
    {
        $oTransaction = oxNew(Transaction::class);
        $oTransaction->load($input);

        $this->assertCount($expected, $oTransaction->getChildTransactions());
    }

    public function testGetChildTransactionsProvider()
    {
        return [
            'with children' => ['1', 2],
            'without children' => ['4', 0],
            'without children (nested)' => ['2', 0],
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
}
