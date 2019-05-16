<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

use Wirecard\Oxid\Model\PaypalPaymentMethod;

/**
 * Acceptance tests for the PayPal checkout flow.
 */
class PaypalCheckoutTest extends CheckoutTestCase
{
    public function getPaymentMethod()
    {
        return new PaypalPaymentMethod();
    }

    public function testCheckoutForPurchase()
    {
        $this->setPaymentActionPurchase();
        $this->goThroughCheckout();

        $this->assertPaymentSuccessful();
    }

    public function testCheckoutForAuthorize()
    {
        $this->setPaymentActionAuthorize();
        $this->goThroughCheckout();

        $this->assertPaymentSuccessful();
    }

    private function goThroughCheckout()
    {
        $this->openShop();
        $this->loginMockUserToFrontend();
        $this->addMockArticleToBasket();

        // Step 1
        $this->continueToNextStep();

        // Step 2
        $this->continueToNextStep();

        // Step 3
        $this->click($this->getLocator('checkout.paymentMethods.paypal'));
        $this->continueToNextStep();

        // Step 4
        $this->continueToNextStep();

        // PayPal flow
        $this->type(
            $this->getLocator('external.paypal.loginEmail'),
            $this->getConfigValue('payments.paypal.email')
        );
        $this->type(
            $this->getLocator('external.paypal.loginPassword'),
            $this->getConfigValue('payments.paypal.password')
        );
        $this->clickAndWait($this->getLocator('external.paypal.loginButton'), 30);
        $this->clickAndWait($this->getLocator('external.paypal.buyNowButton'), 30);

        // Redirect
        $this->waitForRedirectConfirmation();
    }
}
