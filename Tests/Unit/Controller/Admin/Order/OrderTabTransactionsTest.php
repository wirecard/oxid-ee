<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Controller\Admin\Order\OrderTabTransactions;
use Wirecard\Oxid\Tests\Unit\Controller\Admin\TestDataHelper;

class OrderTabTransactionsTest extends Wirecard\Test\WdUnitTestCase
{
    /**
     * @var OrderTabTransactions
     */
    private $_orderTabTransactions;

    protected function dbData()
    {
        return TestDataHelper::getDemoData();
    }

    public function testGetData()
    {
        $_GET['oxid'] = 'oxid 1';

        // use an anonymous class to get access to protected methods
        $cOrderTabTransactions = new class() extends OrderTabTransactions
        {
            public function publicGetData()
            {
                return parent::_getData();
            }
        };

        $this->_orderTabTransactions = $cOrderTabTransactions;

        $aData = $this->_orderTabTransactions->publicGetData();
        $this->assertGreaterThan(0, count($aData));
    }
}