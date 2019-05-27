<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\PayolutionInvoicePaymentMethod;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\PayolutionInvoiceTransaction;

class PayolutionInvoicePaymentMethodTest extends \Wirecard\Test\WdUnitTestCase
{
    /**
     * @var PayolutionInvoicePaymentMethod
     */
    private $_oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->_oPaymentMethod = new PayolutionInvoicePaymentMethod();
    }

    public function testGetConfig()
    {
        $oConfig = $this->_oPaymentMethod->getConfig();
        $this->assertInstanceOf(PaymentMethodConfig::class, $oConfig->get("payolution-inv"));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $this->assertInstanceOf(PayolutionInvoiceTransaction::class, $oTransaction);
    }

    /**
     * @dataProvider getConfigFieldsProvider
     */
    public function testGetConfigFields($sContainsKey)
    {
        $aConfigFields = $this->_oPaymentMethod->getConfigFields();
        $this->assertArrayHasKey($sContainsKey, $aConfigFields);
    }

    public function getConfigFieldsProvider()
    {
        return [
            "contains descriptor" => ['descriptor'],
            "contains additionalInfo" => ['additionalInfo'],
            "contains deleteCanceledOrder" => ['deleteCanceledOrder'],
            "contains deleteFailedOrder" => ['deleteFailedOrder'],
        ];
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

        $this->assertEquals($aExpected, $aPublicFieldNames);
    }
}
