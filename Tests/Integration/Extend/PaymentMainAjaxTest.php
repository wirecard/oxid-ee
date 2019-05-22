<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Extend\Model\Payment;
use Wirecard\Oxid\Extend\PaymentMainAjax;

use Wirecard\Test\WdUnitTestCase;

class PaymentMainAjaxTest extends WdUnitTestCase
{
    /**
     * @var Payment
     */
    private $_payment;

    protected function setUp()
    {
        $this->_payment = oxNew(Payment::class);
        $this->_payment->load("wdpaypal");

        parent::setUp();
    }

    public function testCheckMerchantCredentials()
    {
        oxTestModules::addFunction(
            'oxUtils',
            'showMessageAndExit',
            '{ return $aA; }');


        $this->setRequestParameter('apiUrl', $this->_payment->oxpayments__wdoxidee_apiurl);
        $this->setRequestParameter('httpUser', $this->_payment->oxpayments__wdoxidee_httpuser);
        $this->setRequestParameter('httpPass', $this->_payment->oxpayments__wdoxidee_httppass);

        $oPaymentMainAjax = oxNew(PaymentMainAjax::class);
        $result = $oPaymentMainAjax->checkPaymentMethodCredentials();

        $this->assertTrue(json_decode($result[0])->success);
    }

    public function testCheckInvalidMerchantCredentials()
    {
        oxTestModules::addFunction(
            'oxUtils',
            'showMessageAndExit',
            '{ return $aA; }');
        $this->setRequestParameter('apiUrl', $this->_payment->oxpayments__wdoxidee_apiurl);
        $this->setRequestParameter('httpUser', 'invalid');
        $this->setRequestParameter('httpPass', 'invalid');

        $oPaymentMainAjax = oxNew(PaymentMainAjax::class);
        $result = $oPaymentMainAjax->checkPaymentMethodCredentials();

        $this->assertFalse(json_decode($result[0])->success);
    }
}
