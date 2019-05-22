<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\SepaCreditTransferPaymentMethod;
use Wirecard\Oxid\Model\SepaDirectDebitPaymentMethod;
use Wirecard\Oxid\Model\Transaction;

use OxidEsales\Eshop\Core\Field;

use Wirecard\PaymentSdk\Transaction\SepaDirectDebitTransaction;

class SepaDirectDebitPaymentMethodTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var SepaDirectDebitPaymentMethod
     */
    private $_oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->_oPaymentMethod = new SepaDirectDebitPaymentMethod();
    }

    public function testGetConfig()
    {
        $oConfig = $this->_oPaymentMethod->getConfig();
        $this->assertNotNull($oConfig);
    }

    public function testGetConfigFields()
    {
        $aConfigFields = $this->_oPaymentMethod->getConfigFields();
        $this->assertArrayHasKey("sepaMandateCustom", $aConfigFields);
        $this->assertArrayHasKey("creditorId", $aConfigFields);
        $this->assertArrayHasKey("additionalInfo", $aConfigFields);
        $this->assertArrayHasKey("bic", $aConfigFields);
    }

    public function testGetCheckoutFields()
    {
        $oPayment = $this->_oPaymentMethod->getPayment();
        $oPayment->oxpayments__wdoxidee_bic->value = new Field(1);
        $oPayment->save();
        $aCheckoutFields = $this->_oPaymentMethod->getCheckoutFields();
        $this->assertArrayHasKey("accountHolder", $aCheckoutFields);
        $this->assertArrayHasKey("iban", $aCheckoutFields);
        $this->assertArrayHasKey("bic", $aCheckoutFields);
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $this->assertInstanceOf(SepaDirectDebitTransaction::class, $oTransaction);
    }

    public function testAddMandatoryTransactionData()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $this->assertNotNull($oTransaction->getIban);
    }

    public function testGetPublicFieldNames()
    {
        $aPublicFieldNames = $this->_oPaymentMethod->getPublicFieldNames();
        $this->assertCount(7, $aPublicFieldNames);
        $aExpected = [
            "apiUrl",
            "maid",
            "descriptor",
            "additionalInfo",
            "paymentAction",
            "deleteCanceledOrder",
            "deleteFailedOrder",
        ];
        $this->assertEquals($aExpected, $aPublicFieldNames, '', 0.0, 1, true);
    }

    /**
     * @dataProvider testGetPostProcessingPaymentMethodProvider
     */
    public function testGetPostProcessingPaymentMethod($sAction, $sMethodName)
    {
        $sResult = $this->_oPaymentMethod->getPostProcessingPaymentMethod($sAction);
        $this->assertInstanceOf($sMethodName, $sResult);
    }

    public function testGetPostProcessingPaymentMethodProvider()
    {
        return [
            'credit action' => [Transaction::ACTION_CREDIT, SepaCreditTransferPaymentMethod::class],
            'debit action' => [Transaction::ACTION_PAY, SepaDirectDebitPaymentMethod::class],
        ];
    }

}
