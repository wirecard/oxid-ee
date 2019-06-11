<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Controller\Admin\Order\OrderTabDescriptor;
use Wirecard\Oxid\Tests\Unit\Controller\Admin\TestDataHelper;

class OrderTabDescriptorTest extends Wirecard\Test\WdUnitTestCase
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
        $this->_orderTabDescriptor = new OrderTabDescriptor();
        $this->_orderTabDescriptor->render();

        $aViewData = $this->_orderTabDescriptor->getViewData();

        $this->assertArrayHasKey('emptyText', $aViewData);
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testGetData($sOrderId, $aExpectedArray)
    {
        $_GET['oxid'] = $sOrderId;

        // use an anonymous class to get access to protected methods and variables
        $cOrderTabDescriptor = $this->_getAnonymousOrderTabDescriptor();

        $this->_orderTabDescriptor = $cOrderTabDescriptor;
        $aData = $this->_orderTabDescriptor->publicGetData();

        $this->assertEquals($aData, $aExpectedArray);
    }

    public function getDataProvider()
    {
        return [
            'with descriptor' => ['oxid 3', ['Descriptor']],
            'without descriptor' => ['oxid 2', []],
        ];
    }

    private function _getAnonymousOrderTabDescriptor()
    {
        $cOrderTabDescriptor = new class() extends OrderTabDescriptor
        {
            public function publicGetData()
            {
                return parent::_getData();
            }
        };
        return $cOrderTabDescriptor;
    }
}
