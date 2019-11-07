<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

use Wirecard\Oxid\Model\PaymentMethod\AlipayCrossBorderPaymentMethod;

/**
 * Acceptance tests for the Alipay Cross-border checkout flow.
 */
class AlipayCrossBorderCheckoutTest extends CheckoutTestCase
{
    public function getPaymentMethodName()
    {
        return AlipayCrossBorderPaymentMethod::getName();
    }

    public function testCheckout()
    {
      //  $this->markTestSkipped('must be revisited.');
        $this->goThroughCheckout();
        $this->goThroughExternalFlow();

        // redirect might take a little longer for this payment method as there are multiple redirects
        //$this->waitForRedirectConfirmation(self::WAIT_TIME_EXTERNAL * 2);

      //  $this->assertPaymentSuccessful();
    }

    private function goThroughExternalFlow()
    {
        $this->waitForElement($this->getLocator('external.alipay.accountName'), self::WAIT_TIME_EXTERNAL);
        $this->assertTrue($this->isVisible($this->getLocator('external.alipay.accountName')));
        // We cannot perform the full payment process because of a captcha at Alipay login page
        //        $this->type(
//            $this->getLocator('external.alipay.accountName'),
//            $this->getConfig('payments.alipay.accountName')
//        );
//        $this->type(
//            $this->getLocator('external.alipay.password'),
//            $this->getConfig('payments.alipay.password')
//        );
//        $this->fireEvent($this->getLocator('external.alipay.accountName'), 'blur');
////        $this->waitForElement($this->getLocator('external.alipay.captcha'), self::WAIT_TIME_EXTERNAL);
////        $this->type(
////            $this->getLocator('external.alipay.captcha'),
////            $this->getConfig('payments.alipay.captcha')
////        );
//
//        $this->clickAndWait($this->getLocator('external.alipay.nextStep'), self::WAIT_TIME_EXTERNAL);
//        $this->type(
//            $this->getLocator('external.alipay.paymentPassword'),
//            $this->getConfig('payments.alipay.paymentPassword')
//        );
//        $this->click($this->getLocator('external.alipay.submit'));
    }
}
