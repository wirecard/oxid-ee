<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use \Wirecard\Oxid\Model\Credit_Card_Payment_Method;
use \Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use \OxidEsales\Eshop\Application\Model\Payment;

class Credit_Card_Payment_Method_Test extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var Credit_Card_Payment_Method
     */
    private $oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->oPaymentMethod = new Credit_Card_Payment_Method();
    }

    public function testGetConfig()
    {
        /**
         * @var Payment $oPayment
         */
        $oPayment = oxNew(Payment::class);
        $oPayment->load(Credit_Card_Payment_Method::NAME);

        $oConfig = $this->oPaymentMethod->getConfig($oPayment);
        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('creditcard'));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->oPaymentMethod->getTransaction();
        $this->assertTrue($oTransaction instanceof CreditCardTransaction);
    }
}
