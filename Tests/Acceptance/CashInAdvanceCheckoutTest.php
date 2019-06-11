<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

/**
 * Acceptance tests for OXID's Cash In Advance checkout flow.
 */
class CashInAdvanceCheckoutTest extends CheckoutTestCase
{
    public function getPaymentMethodName()
    {
        return 'oxidpayadvance';
    }

    public function testCheckout()
    {
        $this->goThroughCheckout();

        $this->assertPaymentSuccessful();
    }
}
