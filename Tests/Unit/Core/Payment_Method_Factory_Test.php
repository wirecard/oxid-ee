<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use \Wirecard\Oxid\Core\Payment_Method_Factory;

class Payment_Method_Factory_Test extends OxidEsales\TestingLibrary\UnitTestCase
{
    public function testCreatePaypal()
    {
        $oPaymentMethod = Payment_Method_Factory::create("wdpaypal");
        $this->assertTrue($oPaymentMethod instanceof \Wirecard\Oxid\Model\Paypal_Payment_Method);
    }

    public function testCreateCreditCard()
    {
        $oPaymentMethod = Payment_Method_Factory::create("wdcreditcard");
        $this->assertTrue($oPaymentMethod instanceof \Wirecard\Oxid\Model\Credit_Card_Payment_Method);
    }

}
