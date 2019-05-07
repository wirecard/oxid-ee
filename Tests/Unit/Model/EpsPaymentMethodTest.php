<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\Oxid\Model\EpsPaymentMethod;
use Wirecard\PaymentSdk\Transaction\EpsTransaction;

use OxidEsales\Eshop\Application\Model\Payment;

class EpsPaymentMethodTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var EpsPaymentMethod
     */
    private $oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->oPaymentMethod = new EpsPaymentMethod();
    }

    public function testGetConfig()
    {
        /**
         * @var Payment $oPayment
         */
        $oPayment = PaymentMethodHelper::getPaymentById(EpsPaymentMethod::getName(true));

        $oConfig = $this->oPaymentMethod->getConfig($oPayment);
        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('eps'));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->oPaymentMethod->getTransaction();
        $this->assertInstanceOf(EpsTransaction::class, $oTransaction);
    }

    /**
     * @dataProvider testGetNameProvider
     */
    public function testGetName($bforOxid, $sExpected)
    {
        $sName = EpsPaymentMethod::getName($bforOxid);
        $this->assertEquals($sExpected, $sName);
    }

    public function testGetNameProvider()
    {
        return [
            'for oxid' => [true, 'wdeps'],
            'not for oxid' => [false, 'eps'],
        ];
    }

    public function testGetConfigFields()
    {
        $aConfigFields = $this->oPaymentMethod->getConfigFields();
        $this->assertArrayHasKey('additionalInfo', $aConfigFields);
    }
}
