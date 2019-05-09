<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Controller\Admin\Order\OrderTabTransactionDetails;
use Wirecard\Oxid\Tests\Unit\Controller\Admin\TestDataHelper;

class OrderTabTransactionDetailsTest extends Wirecard\Test\WdUnitTestCase
{
    /**
     * @var OrderTabTransactionDetails
     */
    private $_orderTabTransactionDetails;

    protected function dbData()
    {
        return TestDataHelper::getDemoData();
    }

    public function testGetData()
    {
        $_GET['oxid'] = 'oxid 1';

        // use an anonymous class to get access to protected methods
        $cOrderTabTransactionDetails = new class() extends OrderTabTransactionDetails
        {
            public function publicGetData()
            {
                return parent::_getData();
            }
        };

        $this->_orderTabTransactionDetails = $cOrderTabTransactionDetails;

        $aData = $this->_orderTabTransactionDetails->publicGetData();
        $this->assertGreaterThan(0, count($aData));
    }

    public function testGetDataNoResponseMapper()
    {
        $_GET['oxid'] = 'oxid 1';

        // use an anonymous class to get access to protected methods and variables
        $cOrderTabTransactionDetails = new class() extends OrderTabTransactionDetails
        {
            public function publicGetData()
            {
                return parent::_getData();
            }

            public function setResponseMapper($oResponseMapper)
            {
                $this->oResponseMapper = $oResponseMapper;
            }
        };

        $this->_orderTabTransactionDetails = $cOrderTabTransactionDetails;
        $this->_orderTabTransactionDetails->setResponseMapper(null);

        $aData = $this->_orderTabTransactionDetails->publicGetData();
        $this->assertEquals(0, count($aData));
    }
}