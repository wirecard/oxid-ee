<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\Oxid\Model\PaypalPaymentMethod;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;

use OxidEsales\Eshop\Application\Model\Payment;

class PaypalPaymentMethodTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var PaypalPaymentMethod
     */
    private $_oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->_oPaymentMethod = new PaypalPaymentMethod();
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
        $this->assertInstanceOf(PayPalTransaction::class, $oTransaction);
    }

    public function testGetAdditionalConfigFieldsPaypal()
    {
        $aConfigFields = $this->_oPaymentMethod->getConfigFields();
        $this->assertCount(9, $aConfigFields);
        $this->assertArrayHasKey('basket', $aConfigFields);
    }
}
