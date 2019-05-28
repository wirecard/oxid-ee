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
    private $_oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->_oPaymentMethod = new EpsPaymentMethod();
    }

    public function testGetConfig()
    {
        $oPayment = PaymentMethodHelper::getPaymentById(EpsPaymentMethod::getName(true));

        $oConfig = $this->_oPaymentMethod->getConfig();

        $this->assertInstanceOf(Config::class, $oConfig);
        $this->assertInstanceOf(PaymentMethodConfig::class, $oConfig->get(EpsPaymentMethod::getName()));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $this->assertInstanceOf(EpsTransaction::class, $oTransaction);
    }

    /**
     * @dataProvider getNameProvider
     */
    public function testGetName($bForOxid, $sExpected)
    {
        $sName = EpsPaymentMethod::getName($bForOxid);
        $this->assertEquals($sExpected, $sName);
    }

    public function getNameProvider()
    {
        return [
            'for oxid' => [true, 'wdeps'],
            'not for oxid' => [false, 'eps'],
        ];
    }

    public function testGetConfigFields()
    {
        $aFields = $this->_oPaymentMethod->getConfigFields();
        $this->assertEquals([
            'apiUrl',
            'httpUser',
            'httpPassword',
            'testCredentials',
            'maid',
            'secret',
            'descriptor',
            'additionalInfo',
            'deleteCanceledOrder',
            'deleteFailedOrder',
        ], array_keys($aFields));
    }

    public function testGetPublicFieldNames()
    {
        $aPublicFieldNames = $this->_oPaymentMethod->getPublicFieldNames();
        $aExpected = [
            "apiUrl",
            "maid",
            "descriptor",
            "additionalInfo",
            "deleteCanceledOrder",
            "deleteFailedOrder",
        ];
        $this->assertEquals($aExpected, $aPublicFieldNames, '', 0.0, 1, true);
    }

    public function testGetPostProcessingPaymentMethod()
    {
        $oTransaction = $this->_oPaymentMethod->getPostProcessingPaymentMethod('');
        $this->assertInstanceOf(SepaCreditTransferPaymentMethod::class, $oTransaction);
    }
}
