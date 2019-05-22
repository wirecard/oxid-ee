<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Controller\Admin\TransactionList;
use Wirecard\Oxid\Tests\Unit\Controller\Admin\TestDataHelper;

class TransactionListControllerTest extends Wirecard\Test\WdUnitTestCase
{
    /**
     * @var TransactionList
     */
    private $_transactionList;

    protected function dbData()
    {
        return TestDataHelper::getDemoData();
    }

    /**
     * @dataProvider renderProvider
     */
    public function testRender($sContainsKey)
    {
        $_GET['oxid'] = 'oxid 1';
        $this->_transactionList = new TransactionList();
        $this->_transactionList->render();

        $aViewData = $this->_transactionList->getViewData();

        $this->assertArrayHasKey($sContainsKey, $aViewData);
    }

    public function renderProvider()
    {
        return [
            'contains payments' => ['payments'],
            'contains states' => ['states'],
        ];
    }
}
