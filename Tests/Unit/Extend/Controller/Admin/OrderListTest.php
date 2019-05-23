<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Extend\Controller\Admin\OrderList;

class OrderListTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testRender()
    {
        $oController = oxNew(OrderList::class);
        $sTplFileName = $oController->render();

        $this->assertEquals('order_list.tpl', $sTplFileName);
        $this->assertArrayHasKey('orderStates', $oController->getViewData());
    }
}
