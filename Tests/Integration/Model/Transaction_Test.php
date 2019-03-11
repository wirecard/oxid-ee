<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

class Transaction_Test extends OxidEsales\TestingLibrary\UnitTestCase
{

    public function setUp()
    {
        parent::setUp();

        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $oOrder->save();

        $aTransactionParams = array(
            'testTransactionId',
            $oOrder->getId(),
            100,
            'testTransactionId',
            'testParentTransactionId',
            'testRequestId',
            'reserve',
            'testTransactionType',
            'success',
            12.99,
            'EUR',
            'xmlString',
            date('Y-m-d H:i:s')
        );

        $oDb = oxDb::getDb();
        $oDb->execute(
            "INSERT INTO `wdoxidee_ordertransactions` (`oxid`, `orderid`, `ordernumber`,
                `transactionid`, `parenttransactionid`, `requestid`, `action`, `type`,
                `state`, `amount`, `currency`, `responsexml`, `date`) VALUES (?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            $aTransactionParams
        );
    }

    public function testGetOrder()
    {
        /**
         * @var $oTransaction \Wirecard\Oxid\Model\Transaction
         */
        $oTransaction = oxNew(\Wirecard\Oxid\Model\Transaction::class);
        $oTransaction->load('testTransactionId');
        $orderId = $oTransaction->wdoxidee_ordertransactions__orderid->value;
        $this->assertTrue($oTransaction->isLoaded());
        $oOrder = $oTransaction->getTransactionOrder();
        $this->assertNotNull($oOrder->getId());
    }
}
