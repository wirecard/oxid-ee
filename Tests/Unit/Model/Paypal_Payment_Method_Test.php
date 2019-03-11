<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\Paypal_Payment_Method;

use Wirecard\PaymentSdk\Transaction\PayPalTransaction;

class Paypal_Payment_Method_Test extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var Paypal_Payment_Method
     */
    private $_oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->_oPaymentMethod = new Paypal_Payment_Method;
    }

    public function testGetConfig()
    {
        $oConfig = $this->_oPaymentMethod->getConfig();
        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('paypal'));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $this->assertTrue($oTransaction instanceof \Wirecard\PaymentSdk\Transaction\PayPalTransaction);
    }

    public function testGetCancelTransaction() {
        $oTransaction = $this->_oPaymentMethod->getCancelTransaction();
        $this->assertTrue($oTransaction instanceof \Wirecard\PaymentSdk\Transaction\PayPalTransaction);
    }

    public function testGetRefundTransaction() {
        $oTransaction = $this->_oPaymentMethod->getRefundTransaction();
        $this->assertTrue($oTransaction instanceof \Wirecard\PaymentSdk\Transaction\PayPalTransaction);
    }
}
