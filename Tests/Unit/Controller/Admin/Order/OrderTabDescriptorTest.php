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
     * @var OrderTabDescriptor
     */
    private $_orderTabDescriptor;

    protected function setUp()
    {
        parent::setUp();

        $this->_orderTabDescriptor = oxNew(OrderTabDescriptor::class);

    }

    protected function dbData()
    {
        return TestDataHelper::getDemoData();
    }

    public function testRender()
    {
        $this->setRequestParameter('oxid', 'oxid 1');
        $this->_orderTabDescriptor->render();
        $this->assertArrayHasKey('emptyText', $this->_orderTabDescriptor->getViewData());
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testGetData($sOrderId, $aExpectedArray)
    {
        $this->setRequestParameter('oxid', $sOrderId);

        // use an anonymous class to get access to protected methods and variables
        $cOrderTabDescriptor = $this->_getAnonymousOrderTabDescriptor();
        $aData = $cOrderTabDescriptor->publicGetData();

        $this->assertEquals($aExpectedArray, $aData);
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
