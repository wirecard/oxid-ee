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
    public function testCheckoutForPurchase()
    {
        // set the payment action to "purchase"
        $this->executeSql("UPDATE `oxpayments` SET `WDOXIDEE_TRANSACTIONACTION` = 'pay' WHERE `OXID` LIKE 'wdpaypal'");

        $this->goThroughCheckout();

        $this->assertEquals(true, $this->isThankYouPage($this->getLocation()), 'Payment was not successful.');
    }

    public function testCheckoutForAuthorization()
    {
        // set the payment action to "authorize"
        $this->executeSql("UPDATE `oxpayments` SET `WDOXIDEE_TRANSACTIONACTION` = 'reserve' WHERE `OXID` LIKE 'wdpaypal'");

        $this->goThroughCheckout();

        $this->assertEquals(true, $this->isThankYouPage($this->getLocation()), 'Payment was not successful.');
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
        $this->type($this->getLocator('external.paypal.loginEmail'), $this->getConfigValue('payments.paypal.email'));
        $this->type($this->getLocator('external.paypal.loginPassword'), $this->getConfigValue('payments.paypal.password'));
        $this->clickAndWait($this->getLocator('external.paypal.loginButton'), 30);
        $this->clickAndWait($this->getLocator('external.paypal.buyNowButton'), 30);

        // Redirect
        $this->waitForRedirectConfirmation();
    }
}
