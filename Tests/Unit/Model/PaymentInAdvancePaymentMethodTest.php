<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\PaymentMethod\PaymentInAdvancePaymentMethod;

use Wirecard\PaymentSdk\Config\Config;

class PaymentInAdvancePaymentMethodTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var PaymentInAdvancePaymentMethod
     */
    private $_oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->_oPaymentMethod = new PaymentInAdvancePaymentMethod();
    }

    public function testGetName()
    {
        $sName = PaymentInAdvancePaymentMethod::getName();
        $this->assertEquals('wdpaymentinadvance', $sName);
    }
}
