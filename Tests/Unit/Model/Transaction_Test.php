<?php
/**
 *
 *  Shop System Plugins - Terms of Use
 *
 *  The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 *  of the Wirecard AG range of products and services.
 *
 *  They have been tested and approved for full functionality in the standard configuration
 *  (status on delivery) of the corresponding shop system. They are under General Public
 *  License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 *  the same terms.
 *
 *  However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 *  occurring when used in an enhanced, customized shop system configuration.
 *
 *  Operation in an enhanced, customized configuration is at your own risk and requires a
 *  comprehensive test phase by the user of the plugin.
 *
 *  Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 *  functionality neither does Wirecard AG assume liability for any disadvantages related to
 *  the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 *  for customized shop systems or installed plugins of other vendors of plugins within the same
 *  shop system.
 *
 *  Customers are responsible for testing the plugin's functionality before starting productive
 *  operation.
 *
 *  By installing the plugin into the shop system the customer agrees to these terms of use.
 *  Please do not use the plugin if you do not agree to these terms of use!
 */

class Transaction_Test extends OxidEsales\TestingLibrary\UnitTestCase
{

    public function setUp()
    {
        parent::setUp();
        $oDb = oxDb::getDb();
        $oDb->execute(
            "INSERT INTO `wdoxidee_ordertransactions`(`oxid`, `wdoxidee_orderid`,
            `wdoxidee_requestid`, `wdoxidee_transactiontype`, `wdoxidee_amount`,
            `wdoxidee_refundedamount`, `wdoxidee_currency`, `wdoxidee_transactiondate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            array(
                'testTransactionId', 'testOrderId', 'testRequestId', 'testTransactionType', 12.99, 12.99, 'EUR', date('Y-m-d H:i:s')
            )
        );
    }

    public function testGetOrder()
    {
        /**
         * @var $oTransaction \Wirecard\Oxid\Model\Transaction
         */
        $oTransaction = oxNew(\Wirecard\Oxid\Model\Transaction::class);
        $oTransaction->load('0');
        $this->assertTrue($oTransaction->isLoaded());
        $oOrder = $oTransaction->getOrder();
        var_dump($oTransaction);
        $this->assertTrue($oOrder->getId() === '0');
    }

}
