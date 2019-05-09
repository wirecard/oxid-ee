<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Controller\Admin\Transaction\TransactionTabShipping;
use Wirecard\Oxid\Tests\Unit\Controller\Admin\TestDataHelper;

class TransactionTabShippingTest extends Wirecard\Test\WdUnitTestCase
{
    /**
     * @var TransactionTabShipping
     */
    private $_transactionTabShipping;

    protected function dbData()
    {
        return TestDataHelper::getDemoData();
    }

    public function testGetData()
    {
        $_GET['oxid'] = 'transaction 1';

        // use an anonymous class to get access to protected methods
        $cTransactionTabShipping = new class() extends TransactionTabShipping
        {
            public function publicGetData()
            {
                return parent::_getData();
            }
        };

        $this->_transactionTabShipping = $cTransactionTabShipping;

        $aData = $this->_transactionTabShipping->publicGetData();
        $this->assertGreaterThan(0, count($aData));
    }
}