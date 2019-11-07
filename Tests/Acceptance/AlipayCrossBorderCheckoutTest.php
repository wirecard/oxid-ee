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
        $this->goThroughCheckout();
        $this->goThroughExternalFlow();

        //we cannot test further because there is a capcha and Alipay is unstable, so the test would be very flaky
    }

    private function goThroughExternalFlow()
    {
        //check landing on Alipay page
        $this->waitForElement($this->getLocator('external.alipay.accountName'), self::WAIT_TIME_EXTERNAL * 2);
        $this->assertTrue($this->isVisible($this->getLocator('external.alipay.accountName')));
    }
}
