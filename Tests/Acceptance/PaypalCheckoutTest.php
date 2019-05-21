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
        $this->goThroughExternalFlow();
        $this->waitForRedirectConfirmation();

        $this->assertPaymentSuccessful();
    }

    public function testCheckoutForAuthorize()
    {
        $this->setPaymentActionAuthorize();
        $this->goThroughCheckout();
        $this->goThroughExternalFlow();
        $this->waitForRedirectConfirmation();

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
    }

    private function goThroughExternalFlow()
    {
        $this->waitForElement($this->getLocator('external.paypal.email'), 30);
        $this->type(
            $this->getLocator('external.paypal.email'),
            $this->getConfig('payments.paypal.email')
        );
        $this->type(
            $this->getLocator('external.paypal.password'),
            $this->getConfig('payments.paypal.password')
        );
        $this->clickAndWait($this->getLocator('external.paypal.login'), 30);
        $this->click($this->getLocator('external.paypal.nextStep'));

        if ($this->isElementPresent($this->getLocator('external.paypal.nextStep'))) {
            $this->click($this->getLocator('external.paypal.nextStep'));
        }
    }
}
