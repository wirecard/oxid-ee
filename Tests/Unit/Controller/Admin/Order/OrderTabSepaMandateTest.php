<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Controller\Admin\Order\OrderTabSepaMandate;
use Wirecard\Oxid\Tests\Unit\Controller\Admin\TestDataHelper;

use OxidEsales\Eshop\Core\Field;

class OrderTabSepaMandateTest extends Wirecard\Test\WdUnitTestCase
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
        $this->_orderTabSepaMandate = new OrderTabSepaMandate();
        $this->_orderTabSepaMandate->render();

        $aViewData = $this->_orderTabSepaMandate->getViewData();

        $this->assertGreaterThan(0, count($aViewData));
        $this->assertArrayHasKey('emptyText', $aViewData);
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testGetData($sOrderId, $aExpectedArray)
    {
        $_GET['oxid'] = $sOrderId;

        // use an anonymous class to get access to protected methods and variables
        $cOrderTabSepaMandate = new class() extends OrderTabSepaMandate
        {
            public function publicGetData()
            {
                return parent::_getData();
            }
        };

        $this->_orderTabSepaMandate = $cOrderTabSepaMandate;
        $aData = $this->_orderTabSepaMandate->publicGetData();

        $this->assertEquals($aData, $aExpectedArray);
    }

    public function getDataProvider()
    {
        return [
            'with sepa mandate' => ['oxid 1', ["SEPA mandate test text"]],
            'without sepa mandate' => ['oxid 2', []],
        ];
    }
}
