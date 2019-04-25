<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Test\WdUnitTestCase;

class PaymentMainAjaxTest extends WdUnitTestCase
{

    public function testCheckMerchantCredentials()
    {
        oxTestModules::addFunction(
            'oxUtils',
            'showMessageAndExit',
            '{ return $aA; }');
        $this->setRequestParameter('apiUrl', 'https://api-test.wirecard.com');
        $this->setRequestParameter('httpUser', '70000-APITEST-AP');
        $this->setRequestParameter('httpPass', 'qD2wzQ_hrc!8');

        $oPaymentMainAjax = oxNew(Payment_Main_Ajax::class);
        $result = $oPaymentMainAjax->checkPaymentMethodCredentials();

        $this->assertTrue(json_decode($result[0])->success);
    }

    public function testCheckInvalidMerchantCredentials()
    {
        oxTestModules::addFunction(
            'oxUtils',
            'showMessageAndExit',
            '{ return $aA; }');
        $this->setRequestParameter('apiUrl', 'https://api-test.wirecard.com');
        $this->setRequestParameter('httpUser', 'invalid');
        $this->setRequestParameter('httpPass', 'invalid');

        $oPaymentMainAjax = oxNew(Payment_Main_Ajax::class);
        $result = $oPaymentMainAjax->checkPaymentMethodCredentials();

        $this->assertFalse(json_decode($result[0])->success);
    }
}
