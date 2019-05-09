<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Controller\Admin\Transaction\TransactionTabResponseDetails;
use Wirecard\Oxid\Tests\Unit\Controller\Admin\TestDataHelper;

class TransactionTabResponseDetailsTest extends Wirecard\Test\WdUnitTestCase
{
    /**
     * @var TransactionTabResponseDetails
     */
    private $_transactionTabResponseDetails;

    protected function dbData()
    {
        return TestDataHelper::getDemoData();
    }

    public function testGetData()
    {
        $_GET['oxid'] = 'transaction 1';

        // use an anonymous class to get access to protected methods
        $cTransactionTabResponseDetails = new class() extends TransactionTabResponseDetails
        {
            public function publicGetData()
            {
                return parent::_getData();
            }
        };

        $this->_transactionTabResponseDetails = $cTransactionTabResponseDetails;

        $aData = $this->_transactionTabResponseDetails->publicGetData();
        $this->assertGreaterThan(0, count($aData));
    }

    public function testGetDataNoResponseMapper()
    {
        $_GET['oxid'] = 'transaction 1';

        // use an anonymous class to get access to protected methods and variables
        $cTransactionTabResponseDetails = new class() extends TransactionTabResponseDetails
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

        $this->_transactionTabResponseDetails = $cTransactionTabResponseDetails;
        $this->_transactionTabResponseDetails->setResponseMapper(null);

        $aData = $this->_transactionTabResponseDetails->publicGetData();
        $this->assertEquals(0, count($aData));
    }
}