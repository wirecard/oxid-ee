<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use \Wirecard\Oxid\Model\Paypal_Payment_Method;

class Paypal_Payment_Method_Test extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var Paypal_Payment_Method
     */
    private $oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->oPaymentMethod = new Paypal_Payment_Method;
    }

    public function testGetConfig()
    {
        $oConfig = $this->oPaymentMethod->getConfig();
        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get(Paypal_Payment_Method::NAME));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->oPaymentMethod->getTransaction();
        $this->assertTrue($oTransaction instanceof \Wirecard\PaymentSdk\Transaction\PayPalTransaction);
    }
}
