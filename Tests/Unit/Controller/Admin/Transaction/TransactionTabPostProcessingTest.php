<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Controller\Admin\Transaction\TransactionTabPostProcessing;

class TransactionTabPostProcessingTest extends \Wirecard\Test\WdUnitTestCase
{
    /**
     * @var TransactionTabPostProcessing
     */
    private $_transactionTabPostProcessing;

    protected function dbData()
    {
        return [
            [
                'table' => 'oxorder',
                'columns' => ['oxid', 'oxordernr', 'oxpaymenttype', 'wdoxidee_transactionid'],
                'rows' => [
                    ['oxid 1', 2, 'wdpaypal', 'transaction 1'],
                ]
            ],
            [
                'table' => 'wdoxidee_ordertransactions',
                'columns' => ['oxid', 'orderid', 'ordernumber', 'transactionid', 'parenttransactionid', 'action', 'type', 'state', 'amount', 'currency'],
                'rows' => [
                    ['transaction 1', 'oxid 1', 2, 'transaction 1', null, 'reserve', 'authorization', 'success', 100, 'EUR'],
                ]
            ],
        ];
    }

    public function testRender()
    {
        $this->_transactionTabPostProcessing = new TransactionTabPostProcessing('transaction 1');
        $this->_transactionTabPostProcessing->render();
        $this->assertArrayHasKey('actions', $this->_transactionTabPostProcessing->getViewData());
        $this->assertArrayHasKey('requestParameters', $this->_transactionTabPostProcessing->getViewData());
        $this->assertArrayHasKey('alert', $this->_transactionTabPostProcessing->getViewData());
        $this->assertArrayHasKey('currency', $this->_transactionTabPostProcessing->getViewData());
        $this->assertArrayHasKey('emptyText', $this->_transactionTabPostProcessing->getViewData());
    }
}