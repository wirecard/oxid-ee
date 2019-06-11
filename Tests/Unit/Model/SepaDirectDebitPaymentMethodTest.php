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
use OxidEsales\Eshop\Application\Model\Order;

use Wirecard\Oxid\Model\SepaDirectDebitPaymentMethod;
use Wirecard\Oxid\Model\Transaction;
use Wirecard\Oxid\Tests\Unit\Controller\Admin\TestDataHelper;
use Wirecard\PaymentSdk\Transaction\SepaDirectDebitTransaction;
use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;
use Wirecard\Test\WdUnitTestCase;

class SepaDirectDebitPaymentMethodTest extends Wirecard\Test\WdUnitTestCase
{
    /**
     * @var SepaDirectDebitPaymentMethod
     */
    private $_oPaymentMethod;

    protected function dbData()
    {
        return TestDataHelper::getDemoData();
    }

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
        $aFieldKeys = array_keys($this->_oPaymentMethod->getConfigFields());

        $this->assertEquals([
            'apiUrl',
            'httpUser',
            'httpPassword',
            'testCredentials',
            'maid',
            'secret',
            'descriptor',
            'additionalInfo',
            'deleteCanceledOrder',
            'deleteFailedOrder',
            'bic',
            'paymentAction',
            'creditorId',
            'sepaMandateCustom',
        ], $aFieldKeys);
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
    public function testAddMandatoryTransactionData($sAttribute, $sField)
    {
        $aDynvalue = [
            $sAttribute => $sField,
        ];

        Registry::getConfig()->getSession()->setVariable('dynvalue', $aDynvalue);
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $oOrder = oxNew(Order::class);
        $this->_oPaymentMethod->addMandatoryTransactionData($oTransaction, $oOrder);

        $this->assertAttributeEquals($sField, $sAttribute, $oTransaction);
    }

    public function addMandatoryTransactionDataProvider()
    {
        return [
            'contains iban' => ['iban', 'myiban'],
            'contains bic' => ['bic', 'mybic'],
        ];
    }

    /**
     * @dataProvider addMandatoryTransactionDataNotEmptyProvider
     */
    public function testAddMandatoryTransactionDataNotEmpty($sAttribute)
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $oOrder = oxNew(Order::class);
        $this->_oPaymentMethod->addMandatoryTransactionData($oTransaction, $oOrder);

        $this->assertAttributeNotEmpty($sAttribute, $oTransaction);
    }

    public function addMandatoryTransactionDataNotEmptyProvider()
    {
        return [
            'mandate not empty' => ['mandate'],
            'accountHolder not empty' => ['accountHolder'],
        ];
    }

    public function testGetPublicFieldNames()
    {
        $this->assertEquals([
            'apiUrl',
            'maid',
            'descriptor',
            'additionalInfo',
            'paymentAction',
            'deleteCanceledOrder',
            'deleteFailedOrder',
        ], $this->_oPaymentMethod->getPublicFieldNames());
    }

    public function testGetCheckoutFieldsWithoutBic()
    {
        $aFieldKeys = array_keys($this->_oPaymentMethod->getCheckoutFields());

        $this->assertEquals([
            'accountHolder',
            'iban',
        ], $aFieldKeys);
    }

    public function testGetCheckoutFieldsWithBic()
    {
        $oPayment = $this->_oPaymentMethod->getPayment();
        $oPayment->oxpayments__wdoxidee_bic->value = '1';
        $aFieldKeys = array_keys($this->_oPaymentMethod->getCheckoutFields());

        $this->assertEquals([
            'accountHolder',
            'iban',
            'bic',
        ], $aFieldKeys);
    }

    /**
     * @dataProvider getPostProcessingTransactionProvider
     */
    public function testGetPostProcessingTransaction($sAction, $sClassName)
    {
        $oParentTransaction = oxNew(Transaction::class);
        $oParentTransaction->loadWithTransactionId('transaction 1');

        $sResult = $this->_oPaymentMethod->getPostProcessingTransaction($sAction, $oParentTransaction);

        $this->assertInstanceOf($sClassName, $sResult);
    }

    public function getPostProcessingTransactionProvider()
    {
        return [
            'refund action' => [
                Transaction::ACTION_CREDIT,
                SepaCreditTransferTransaction::class,
            ],
            'non-refund action' => [
                Transaction::ACTION_RESERVE,
                SepaDirectDebitTransaction::class,
            ],
        ];
    }

    public function testOnBeforeTransactionCreationWithRequestParameter()
    {
        $this->setRequestParameter('wdsepadd_checkbox', true);

        $this->assertNull($this->_oPaymentMethod->onBeforeTransactionCreation());
    }

    /**
     * @expectedException OxidEsales\Eshop\Core\Exception\InputException
     */
    public function testOnBeforeTransactionCreationWithoutRequestParameter()
    {
        $this->_oPaymentMethod->onBeforeTransactionCreation();
    }

}
