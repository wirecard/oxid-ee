<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Core\Field;

use Wirecard\Oxid\Model\SepaCreditTransferPaymentMethod;
use Wirecard\Oxid\Model\Transaction;
use Wirecard\Oxid\Tests\Unit\Controller\Admin\TestDataHelper;
use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;
use Wirecard\Test\WdUnitTestCase;

class SepaCreditTransferPaymentMethodTest extends Wirecard\Test\WdUnitTestCase
{
    /**
     * @var SepaCreditTransferPaymentMethod
     */
    private $_oPaymentMethod;

    protected function dbData()
    {
        return TestDataHelper::getDemoData();
    }

    protected function setUp()
    {
        parent::setUp();
        $this->_oPaymentMethod = new SepaCreditTransferPaymentMethod();
    }

    public function testGetConfig()
    {
        $oConfig = $this->_oPaymentMethod->getConfig();
        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('sepacredit'));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $this->assertInstanceOf(SepaCreditTransferTransaction::class, $oTransaction);
    }

    public function testGetPublicFieldNames()
    {
        $aPublicFieldNames = $this->_oPaymentMethod->getPublicFieldNames();
        $this->assertCount(2, $aPublicFieldNames);
        $aExpected = [
            "apiUrl",
            "maid",
        ];
        $this->assertEquals($aExpected, $aPublicFieldNames, '', 0.0, 1, true);
    }

    public function testIsMerchantOnly()
    {
        $this->assertTrue($this->_oPaymentMethod->isMerchantOnly());
    }

    public function testGetPostProcessingTransaction()
    {
        $oParentTransaction = oxNew(Transaction::class);
        $oParentTransaction->loadWithTransactionId('transaction 1');

        $this->assertInstanceOf(
            SepaCreditTransferTransaction::class,
            $this->_oPaymentMethod->getPostProcessingTransaction('', $oParentTransaction)
        );
    }
}
