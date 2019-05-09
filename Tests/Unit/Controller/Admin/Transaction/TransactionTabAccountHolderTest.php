<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Controller\Admin\Transaction\TransactionTabAccountHolder;
use Wirecard\Oxid\Tests\Unit\Controller\Admin\TestDataHelper;

class TransactionTabAccountHolderTest extends Wirecard\Test\WdUnitTestCase
{
    /**
     * @var TransactionTabAccountHolder
     */
    private $_transactionTabAccountHolder;

    protected function dbData()
    {
        return TestDataHelper::getDemoData();
    }

    public function testGetData()
    {
        $_GET['oxid'] = 'transaction 1';

        // use an anonymous class to get access to protected methods
        $cTransactionTabAccountHolder = new class() extends TransactionTabAccountHolder
        {
            public function publicGetData()
            {
                return parent::_getData();
            }
        };

        $this->_transactionTabAccountHolder = $cTransactionTabAccountHolder;

        $aData = $this->_transactionTabAccountHolder->publicGetData();
        $this->assertGreaterThan(0, count($aData));
    }
}