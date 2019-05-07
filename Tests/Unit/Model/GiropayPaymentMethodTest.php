<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Application\Model\Payment;

use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\Oxid\Model\GiropayPaymentMethod;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\GiropayTransaction;

class GiropayPaymentMethodTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var GiropayPaymentMethod
     */
    private $_oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();

        $this->_oPaymentMethod = new GiropayPaymentMethod();
    }

    public function testGetConfig()
    {
        /**
         * @var Payment $oPayment
         */
        $oPayment = PaymentMethodHelper::getPaymentById(GiropayPaymentMethod::getName(true));
        $oConfig = $this->_oPaymentMethod->getConfig($oPayment);

        $this->assertInstanceOf(Config::class, $oConfig);
        $this->assertInstanceOf(PaymentMethodConfig::class, $oConfig->get(GiropayPaymentMethod::getName()));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();

        $this->assertInstanceOf(GiropayTransaction::class, $oTransaction);
        $this->assertObjectHasAttribute('bankData', $oTransaction);
    }

    public function testGetConfigFields()
    {
        $aConfigFields = $this->_oPaymentMethod->getConfigFields();

        $this->assertEquals(array_keys($aConfigFields), [
            'apiUrl',
            'httpUser',
            'httpPassword',
            'testCredentials',
            'maid',
            'secret',
            'additionalInfo',
            'deleteCanceledOrder',
            'deleteFailedOrder',
        ]);
    }

    public function testGetCheckoutFields()
    {
        $aCheckoutFields = $this->_oPaymentMethod->getCheckoutFields();

        $this->assertEquals(array_keys($aCheckoutFields), [
            'bic',
        ]);
    }
}
