<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

use Wirecard\Oxid\Model\PaymentMethod\PaymentOnInvoicePaymentMethod;

/**
 * Acceptance tests for the Payment on Invoice checkout flow.
 */
class PaymentOnInvoiceCheckoutTest extends CheckoutTestCase
{
    public function getPaymentMethodName()
    {
        return PaymentOnInvoicePaymentMethod::getName();
    }

    public function testCheckout()
    {
        $this->goThroughCheckout();
        $this->waitForRedirectConfirmation();

        $this->assertPaymentSuccessful();
    }
}
