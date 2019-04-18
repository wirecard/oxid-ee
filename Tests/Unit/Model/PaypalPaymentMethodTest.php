<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\PaypalPaymentMethod;

use Wirecard\PaymentSdk\Transaction\PayPalTransaction;

class PaypalPaymentMethodTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var PaypalPaymentMethod
     */
    private $oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->oPaymentMethod = new PaypalPaymentMethod;
    }

    public function testGetConfig()
    {
        $oConfig = $this->oPaymentMethod->getConfig();
        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('paypal'));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->oPaymentMethod->getTransaction();
        $this->assertInstanceOf(PayPalTransaction::class, $oTransaction);
    }
}
