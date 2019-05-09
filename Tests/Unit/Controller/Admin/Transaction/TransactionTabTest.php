<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Controller\Admin\Transaction\TransactionTab;
use Wirecard\Oxid\Tests\Unit\Controller\Admin\Transaction\TransactionTestHelper;

class TransactionTabTest extends Wirecard\Test\WdUnitTestCase
{
    /**
     * @var TransactionTab
     */
    private $_transactionTab;

    protected function dbData()
    {
        return TransactionTestHelper::getDemoData();
    }

    public function testGetListData()
    {
        $_GET['oxid'] = 'transaction 1';

        // use an anonymous class to get access to protected methods
        $cTransactionTab = new class() extends TransactionTab
        {
            public function publicGetListDataFromArray(array $aArray, string $sTransactionState = null): array
            {
                return parent::_getListDataFromArray($aArray, $sTransactionState);
            }
        };

        $this->_transactionTab = $cTransactionTab;

        $aArray = [
            'transactionId' => 'transaction 1',
            'transactionState' => 'awaiting',
        ];

        $aListData = $this->_transactionTab->publicGetListDataFromArray($aArray, 'awaiting');

        $this->assertEquals(count($aArray), count($aListData));

        foreach ($aListData as $aListItem) {
            $this->assertArrayHasKey('title', $aListItem);
            $this->assertArrayHasKey('value', $aListItem);
        }
    }
}