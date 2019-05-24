<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use Wirecard\Oxid\Model\SepaCreditTransferPaymentMethod;
use Wirecard\Oxid\Model\SepaDirectDebitPaymentMethod;
use Wirecard\Oxid\Model\Transaction;
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

    /**
     * @dataProvider getConfigFieldsProvider
     */
    public function testGetConfigFields($sContainsKey)
    {
        $aConfigFields = $this->_oPaymentMethod->getConfigFields();
        $this->assertArrayHasKey($sContainsKey, $aConfigFields);
    }

    public function getConfigFieldsProvider()
    {
        return [
            'contains sepaMandateCustom' => ['sepaMandateCustom'],
            'contains creditorId' => ['creditorId'],
            'contains additionalInfo' => ['additionalInfo'],
            'contains bic' => ['bic'],
        ];
    }

    /**
     * @dataProvider getCheckoutFieldsProvider
     */
    public function testGetCheckoutFields($sContainsKey)
    {
        $oPayment = $this->_oPaymentMethod->getPayment();
        $oPayment->oxpayments__wdoxidee_bic->value = new Field(1);
        $oPayment->save();
        $aCheckoutFields = $this->_oPaymentMethod->getCheckoutFields();
        $this->assertArrayHasKey($sContainsKey, $aCheckoutFields);
    }

    public function getCheckoutFieldsProvider()
    {
        return [
            'contains accountHolder' => ['accountHolder'],
            'contains iban' => ['iban'],
            'contains bic' => ['bic'],
        ];
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $this->assertInstanceOf(SepaDirectDebitTransaction::class, $oTransaction);
    }

    /**
     * @dataProvider addMandatoryTransactionDataProvider
     */
    public function testAddMandatoryTransactionData($sAttribute)
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();

        $aDynArray = [
            'bic' => 'WIREDEMMXXX',
            'iban' => "IBAN"];
        Registry::getSession()->setVariable('dynvalue', $aDynArray);
        $this->_oPaymentMethod->addMandatoryTransactionData($oTransaction);

        $this->assertAttributeNotEmpty($sAttribute, $oTransaction);
    }

    public function addMandatoryTransactionDataProvider()
    {
        return [
            'contains iban' => ['iban'],
            'contains mandate' => ['mandate'],
            'contains accountHolder' => ['accountHolder'],
        ];
    }

    public function testGetPublicFieldNames()
    {
        $aPublicFieldNames = $this->_oPaymentMethod->getPublicFieldNames();
        $aExpected = [
            "apiUrl",
            "maid",
            "descriptor",
            "additionalInfo",
            "paymentAction",
            "deleteCanceledOrder",
            "deleteFailedOrder",
        ];
        $this->assertEquals($aExpected, $aPublicFieldNames);
    }

    /**
     * @dataProvider getPostProcessingPaymentMethodProvider
     */
    public function testGetPostProcessingPaymentMethod($sAction, $sMethodName)
    {
        $sResult = $this->_oPaymentMethod->getPostProcessingPaymentMethod($sAction);
        $this->assertInstanceOf($sMethodName, $sResult);
    }

    public function getPostProcessingPaymentMethodProvider()
    {
        return [
            'credit action' => [Transaction::ACTION_CREDIT, SepaCreditTransferPaymentMethod::class],
            'debit action' => [Transaction::ACTION_PAY, SepaDirectDebitPaymentMethod::class],
        ];
    }

}
