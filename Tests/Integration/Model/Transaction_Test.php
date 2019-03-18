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

        $oDb = oxDb::getDb();
        $oDb->execute(
            "INSERT INTO `wdoxidee_ordertransactions`(`oxid`, `wdoxidee_orderid`,
            `wdoxidee_requestid`, `wdoxidee_transactiontype`, `wdoxidee_amount`,
            `wdoxidee_refundedamount`, `wdoxidee_currency`, `wdoxidee_transactiondate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            array(
                'testTransactionId', $oOrder->getId(), 'testRequestId', 'testTransactionType', 12.99, 12.99, 'EUR', date('Y-m-d H:i:s')
            )
        );
    }

    public function testGetOrder()
    {

        /**
         * @var $oTransaction \Wirecard\Oxid\Model\Transaction
         */
        $oTransaction = oxNew(\Wirecard\Oxid\Model\Transaction::class);
        $oTransaction->load('testTransactionId');
        $orderId = $oTransaction->wdoxidee_ordertransactions__wdoxidee_orderid->value;
        $this->assertTrue($oTransaction->isLoaded());
        $oOrder = $oTransaction->getOrder();
        $this->assertNotNull($oOrder->getId());
    }

}
