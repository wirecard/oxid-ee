<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

use Wirecard\Oxid\Model\PaymentInAdvancePaymentMethod;

/**
 * Acceptance tests for the Payment in Advance checkout flow.
 */
class PaymentInAdvanceCheckoutTest extends CheckoutTestCase
{
    public function getPaymentMethodName()
    {
        return PaymentInAdvancePaymentMethod::getName(true);
    }

    public function testCheckout()
    {
        $this->goThroughCheckout();
        $this->waitForRedirectConfirmation();
        $this->assertElementPresent($this->getLocator('checkout.piaTable'));

        $this->assertPaymentSuccessful();
    }
}
