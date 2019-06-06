<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Application\Model\Order;

use Wirecard\Oxid\Model\RatepayInvoicePaymentMethod;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;

class RatepayInvoicePaymentMethodTest extends OxidEsales\TestingLibrary\UnitTestCase
{

    /**
     * @var RatepayInvoicePaymentMethod
     */
    private $_oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->_oPaymentMethod = new RatepayInvoicePaymentMethod();
    }

    public function testGetConfig()
    {
        $oConfig = $this->_oPaymentMethod->getConfig();

        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('ratepay-invoice'));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $this->assertInstanceOf(RatepayInvoiceTransaction::class, $oTransaction);
    }

    public function testIsMerchantOnly()
    {
        $this->assertFalse($this->_oPaymentMethod->isMerchantOnly());
    }

    /**
     * @dataProvider getNameProvider
     */
    public function testGetName($bforOxid, $sExpected)
    {
        $sName = RatepayInvoicePaymentMethod::getName($bforOxid);
        $this->assertEquals($sExpected, $sName);
    }

    public function getNameProvider()
    {
        return [
            'for oxid' => [true, 'wdratepay-invoice'],
            'not for oxid' => [false, 'ratepay-invoice'],
        ];
    }

    public function testAddMandatoryTransactionData()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $oOrder = oxNew(Order::class);
        $this->_oPaymentMethod->addMandatoryTransactionData($oTransaction, $oOrder);

        $this->assertObjectHasAttribute('shipping', $oTransaction);
    }

    public function testGetConfigFields()
    {
        $aFields = $this->_oPaymentMethod->getConfigFields();

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
            'allowedCurrencies',
            'shippingCountries',
            'billingCountries',
            'billingShipping',
        ], array_keys($aFields));
    }

    public function testGetPublicFieldNames()
    {
        $aFieldNames = $this->_oPaymentMethod->getPublicFieldNames();

        $this->assertEquals([
            'apiUrl',
            'maid',
            'descriptor',
            'additionalInfo',
            'deleteCanceledOrder',
            'deleteFailedOrder',
            'allowedCurrencies',
            'shippingCountries',
            'billingCountries',
            'billingShipping',
        ], $aFieldNames);
    }

    public function testGetMetaDataFieldNames()
    {
        $this->assertEquals([
            'allowed_currencies',
            'shipping_countries',
            'billing_countries',
            'billing_shipping',
        ], $this->_oPaymentMethod->getMetaDataFieldNames());
    }
}
