<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\SepaCreditTransferPaymentMethod;
use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;
use OxidEsales\Eshop\Core\Field;

class SepaCreditTransferPaymentMethodTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var SepaCreditTransferPaymentMethod
     */
    private $_oPaymentMethod;

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

    public function testAddPostProcessingTransactionData()
    {
        try {
            $oTransaction = $this->_oPaymentMethod->getTransaction();
            $oParentTransaction = $this->_oPaymentMethod->getTransaction();
            $oParentTransaction->wdoxidee_ordertransactions__orderid = new Field('testid');
            $this->_oPaymentMethod->addPostProcessingTransactionData($oTransaction, $oParentTransaction);
        } catch (Exception $exception) {
            assert('Exception catched in testAddPostProcessingTransactionData');
        }
    }
}
