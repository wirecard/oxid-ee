<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Controller\Admin\Transaction\TransactionTabTransactionDetails;
use Wirecard\Oxid\Tests\Unit\Controller\Admin\Transaction\TransactionTestHelper;

class TransactionTabTransactionDetailsTest extends Wirecard\Test\WdUnitTestCase
{
    /**
     * @var TransactionTabTransactionDetails
     */
    private $_transactionTabTransactionDetails;

    protected function dbData()
    {
        return TransactionTestHelper::getDemoData();
    }

    public function testGetData()
    {
        $_GET['oxid'] = 'transaction 1';

        // use an anonymous class to get access to protected methods
        $cTransactionTabTransactionDetails = new class() extends TransactionTabTransactionDetails
        {
            public function publicGetData()
            {
                return parent::_getData();
            }
        };

        $this->_transactionTabTransactionDetails = $cTransactionTabTransactionDetails;

        $aData = $this->_transactionTabTransactionDetails->publicGetData();
        $this->assertGreaterThan(0, count($aData));
    }
}