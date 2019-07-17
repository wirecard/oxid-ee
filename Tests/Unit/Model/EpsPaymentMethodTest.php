<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\Oxid\Model\PaymentMethod\EpsPaymentMethod;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\EpsTransaction;

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
        $oConfig = $this->_oPaymentMethod->getConfig();

        $this->assertInstanceOf(Config::class, $oConfig);
        $this->assertInstanceOf(PaymentMethodConfig::class, $oConfig->get('eps'));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $this->assertInstanceOf(EpsTransaction::class, $oTransaction);
    }

    public function testGetName()
    {
        $sName = EpsPaymentMethod::getName();
        $this->assertEquals('wdeps', $sName);
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
}
