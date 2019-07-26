<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Application\Model\Order;

use Wirecard\Oxid\Model\PaymentMethod\BasePoiPiaPaymentMethod;
use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\Oxid\Model\Transaction;

class TransactionTest extends Wirecard\Test\WdUnitTestCase
{
    protected function dbData()
    {
        return [
            [
                'table' => 'oxorder',
                'columns' => [
                    'oxid',
                    'oxpaymenttype',
                ],
                'rows' => [
                    ['order 1', 'wdpaymentinadvance'],
                    ['order 2', 'wdpaymentoninvoice'],
                    ['order 3', 'wdcreditcard'],
                ],
            ],
            [
                'table' => 'wdoxidee_ordertransactions',
                'columns' => [
                    'oxid',
                    'orderid',
                    'transactionid',
                    'parenttransactionid',
                    'responsexml',
                    'state',
                ],
                'rows' => [
                    ['1', 'order 1', '1', null, base64_encode('<?xml version="1.0" encoding="UTF-8"?>'), 'success'],
                    ['2', 'order 1', '1.1', '1', '', 'success'],
                    ['3', 'order 1', '1.2', '1', '', 'success'],
                    ['4', 'order 1', '2', null, '', 'success'],
                    ['5', 'order 1', '3', null, '', 'success'],
                    ['6', 'order 1', 't4', null, '', 'success'],
                    ['7', 'order 2', 't5', null, '', 'awaiting'],
                    ['8', 'order 3', 't6', null, '', 'xxx'],
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

    public function testGetTranslatedStates()
    {
        $oTransaction = oxNew(Transaction::class);
        $aTransactionStates = $oTransaction->getTranslatedStates();
        $this->assertCount(4, $aTransactionStates);
    }

    /**
     * @dataProvider getTranslatedStateProvider
     */
    public function testGetTranslatedState($sExpected, $sTransactionId)
    {
        $oTransaction = oxNew(Transaction::class);
        $oTransaction->loadWithTransactionId($sTransactionId);
        $this->assertEquals($sExpected, $oTransaction->getTranslatedState());
    }

    public function getTranslatedStateProvider()
    {
        return [
            'translated success' => ['erfolgreich', 't4'],
            'translated awaiting' => ['ausstehend', 't5'],
            'no translation found' => ['', 't6'],
        ];
    }
}
