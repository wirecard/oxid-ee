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
 * Acceptance tests for the PayPal checkout flow.
 */
class PayPalCheckoutTest extends CheckoutTestCase
{
    public function testCheckout()
    {
        $this->openShop();
        $this->loginMockUserToFrontend();
        $this->addMockArticleToBasket();

        // Step 1
        $this->continueToNextStep();

        // Step 2
        $this->continueToNextStep();

        // Step 3
        $this->click('payment_wdpaypal');
        $this->continueToNextStep();

        // Step 4
        $this->continueToNextStep();

        // PayPal flow
        $this->type('email', $this->getConfigValue('payments.paypal.email'));
        $this->type('password', $this->getConfigValue('payments.paypal.password'));
        $this->clickAndWait('btnLogin', 30);
        $this->clickAndWait('confirmButtonTop', 30);

        // Redirect
        $this->waitForRedirectConfirmation();

        $this->assertEquals(true, $this->isThankYouPage($this->getLocation()), 'Payment was not successful.');
    }
}
