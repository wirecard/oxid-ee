<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\PaymentMethod\PaymentOnInvoicePaymentMethod;

use Wirecard\PaymentSdk\Config\Config;

class PaymentOnInvoicePaymentMethodTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var PaymentOnInvoicePaymentMethod
     */
    private $_oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->_oPaymentMethod = new PaymentOnInvoicePaymentMethod();
    }

    public function testGetName()
    {
        $sName = PaymentOnInvoicePaymentMethod::getName();
        $this->assertEquals('wdpaymentoninvoice', $sName);
    }
}
