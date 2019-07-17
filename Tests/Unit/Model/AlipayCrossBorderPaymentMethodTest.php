<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\PaymentMethod\AlipayCrossBorderPaymentMethod;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\AlipayCrossborderTransaction;

use OxidEsales\Eshop\Application\Model\Order;

class AlipayCrossBorderPaymentMethodTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var AlipayCrossBorderPaymentMethod
     */
    private $_oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();

        $this->_oPaymentMethod = new AlipayCrossBorderPaymentMethod();
    }

    public function testGetConfig()
    {
        $oConfig = $this->_oPaymentMethod->getConfig();

        $this->assertInstanceOf(Config::class, $oConfig);
        $this->assertInstanceOf(PaymentMethodConfig::class, $oConfig->get('alipay-xborder'));
    }

    public function testGetTransaction()
    {
        $this->assertInstanceOf(AlipayCrossborderTransaction::class, $this->_oPaymentMethod->getTransaction());
    }

    public function testAddMandatoryTransactionData()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $oOrder = oxNew(Order::class);
        $oOrder->oxorder__oxpaymenttype = new \OxidEsales\Eshop\Core\Field('wdalipay-xborder');
        $this->_oPaymentMethod->addMandatoryTransactionData($oTransaction, $oOrder);

        $this->assertObjectHasAttribute('accountHolder', $oTransaction);
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
        $aFieldNames = $this->_oPaymentMethod->getPublicFieldNames();

        $this->assertEquals([
            'apiUrl',
            'maid',
            'descriptor',
            'additionalInfo',
            'deleteCanceledOrder',
            'deleteFailedOrder',
        ], $aFieldNames);
    }
}
