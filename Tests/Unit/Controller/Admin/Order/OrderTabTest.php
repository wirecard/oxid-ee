<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Controller\Admin\Order\OrderTab;
use Wirecard\Oxid\Tests\Unit\Controller\Admin\TestDataHelper;

class OrderTabTest extends Wirecard\Test\WdUnitTestCase
{
    /**
     * @var OrderTab
     */
    private $_orderTab;

    protected function dbData()
    {
        return TestDataHelper::getDemoData();
    }

    public function testRender()
    {
        $_GET['oxid'] = 'oxid 1';
        $this->_orderTab = new OrderTab();
        $this->_orderTab->render();

        $aViewData = $this->_orderTab->getViewData();

        $this->assertGreaterThan(0, count($aViewData));
        $this->assertArrayHasKey('emptyText', $aViewData);
    }
}