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
use Wirecard\Oxid\Model\SepaCreditTransferPaymentMethod;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
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
        $oPayment = PaymentMethodHelper::getPaymentById(EpsPaymentMethod::getName(true));

        $oConfig = $this->oPaymentMethod->getConfig();

        $this->assertInstanceOf(Config::class, $oConfig);
        $this->assertInstanceOf(PaymentMethodConfig::class, $oConfig->get(EpsPaymentMethod::getName()));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->oPaymentMethod->getTransaction();
        $this->assertInstanceOf(EpsTransaction::class, $oTransaction);
    }

    /**
     * @dataProvider getNameProvider
     */
    public function testGetName($sExpected)
    {
        $sName = EpsPaymentMethod::getName();
        $this->assertEquals($sExpected, $sName);
    }

    public function getNameProvider()
    {
        return [
            'correct payment name' => ['eps'],
        ];
    }

    public function testGetConfigFields()
    {
        $aConfigFields = $this->oPaymentMethod->getConfigFields();
        $this->assertArrayHasKey('additionalInfo', $aConfigFields);
    }

    public function testGetPublicFieldNames()
    {
        $aFieldNames = $this->oPaymentMethod->getPublicFieldNames();
        $this->assertNotNull($aFieldNames);
    }

    public function testGetPostProcessingPaymentMethod()
    {
        $oTransaction = $this->oPaymentMethod->getPostProcessingPaymentMethod('');
        $this->assertInstanceOf(SepaCreditTransferPaymentMethod::class, $oTransaction);
    }
}
