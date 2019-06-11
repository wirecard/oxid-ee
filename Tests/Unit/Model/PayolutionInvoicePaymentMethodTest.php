<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\PayolutionInvoicePaymentMethod;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\PayolutionInvoiceTransaction;

class PayolutionInvoicePaymentMethodTest extends \Wirecard\Test\WdUnitTestCase
{
    /**
     * @var PayolutionInvoicePaymentMethod
     */
    private $_oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->_oPaymentMethod = new PayolutionInvoicePaymentMethod();
    }

    public function testGetConfig()
    {
        $oConfig = $this->_oPaymentMethod->getConfig();
        $this->assertInstanceOf(PaymentMethodConfig::class, $oConfig->get("payolution-inv"));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $this->assertInstanceOf(PayolutionInvoiceTransaction::class, $oTransaction);
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
            'shippingCountries',
            'billingCountries',
            'billingShipping',
            'trustedShop',
            'payolutionTermsUrl',
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
            'shippingCountries',
            'billingCountries',
            'billingShipping',
            'trustedShop',
            'payolutionTermsUrl',
        ], $aFieldNames);
    }

    public function testGetMetaDataFieldNames()
    {
        $this->assertEquals([
            'shipping_countries',
            'billing_countries',
            'billing_shipping',
            'trusted_shop',
            'payolution_terms_url',
        ], $this->_oPaymentMethod->getMetaDataFieldNames());
    }

    public function testOnBeforeTransactionCreationWithRequestParameter()
    {
        $this->setRequestParameter('trustedshop_checkbox', true);

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
