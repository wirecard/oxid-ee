<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Controller\Admin\Transaction\TransactionTabPaymentDetails;
use Wirecard\Oxid\Tests\Unit\Controller\Admin\Transaction\TransactionTestHelper;

class TransactionTabPaymentDetailsTest extends Wirecard\Test\WdUnitTestCase
{
    /**
     * @var TransactionTabPaymentDetails
     */
    private $_transactionTabPaymentDetails;

    protected function dbData()
    {
        return TransactionTestHelper::getDemoData();
    }

    public function testGetData()
    {
        $_GET['oxid'] = 'transaction 1';

        // use an anonymous class to get access to protected methods
        $cTransactionTabPaymentDetails = new class() extends TransactionTabPaymentDetails
        {
            public function publicGetData()
            {
                return parent::_getData();
            }
        };

        $this->_transactionTabPaymentDetails = $cTransactionTabPaymentDetails;

        $aData = $this->_transactionTabPaymentDetails->publicGetData();
        $this->assertGreaterThan(0, count($aData));
    }
}