<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\SofortPaymentMethod;

use Wirecard\PaymentSdk\Transaction\SofortTransaction;

use OxidEsales\Eshop\Application\Model\Payment;

class SofortPaymentMethodTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var SofortPaymentMethod
     */
    private $oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->oPaymentMethod = new SofortPaymentMethod();
    }

    public function testGetConfig()
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->load(SofortPaymentMethod::getName(true));
        $oConfig = $this->oPaymentMethod->getConfig($oPayment);
        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('sofortbanking'));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->oPaymentMethod->getTransaction();
        $this->assertInstanceOf(SofortTransaction::class, $oTransaction);
    }
}
