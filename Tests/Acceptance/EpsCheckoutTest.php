<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

use Wirecard\Oxid\Model\EpsPaymentMethod;

/**
 * Acceptance tests for the Eps checkout flow.
 */
class EpsCheckoutTest extends CheckoutTestCase
{
    public function getPaymentMethod()
    {
        return new EpsPaymentMethod();
    }

    public function testCheckout()
    {
        $this->goThroughCheckout();
        $this->goThroughExternalFlow();
        $this->waitForRedirectConfirmation();

        $this->assertPaymentSuccessful();
    }

    public function goThroughCheckout()
    {
        $this->openShop();
        $this->loginMockUserToFrontend();
        $this->addMockArticleToBasket();

        // Step 1: Cart
        $this->continueToNextStep();

        // Step 2: Address
        $this->continueToNextStep();

        // Step 3: Pay
        $this->click(sprintf(
            $this->getLocator('checkout.paymentMethod'),
            $this->paymentMethod::getName(true)
        ));
        $this->continueToNextStep();

        // Step 4: Order
        $this->continueToNextStep();
    }

    private function goThroughExternalFlow()
    {
        $this->waitForElement($this->getLocator('external.eps.bic'), 30);
        $this->type(
            $this->getLocator('external.eps.bic'),
            $this->getConfig('payments.eps.bic')
        );
        $this->clickAndWait($this->getLocator('external.eps.submitBic'));

        $this->waitForElement($this->getLocator('external.eps.id'), 30);
        $this->type(
            $this->getLocator('external.eps.id'),
            $this->getConfig('payments.eps.id')
        );

        $this->waitForElement($this->getLocator('external.eps.submitLogin'), 30);
        $this->clickAndWait($this->getLocator('external.eps.submitLogin'));
        $this->waitForElement($this->getLocator('external.eps.signPayment'), 30);
        $this->clickAndWait($this->getLocator('external.eps.signPayment'));
        $this->waitForElement($this->getLocator('external.eps.finalize'), 30);
        $this->clickAndWait($this->getLocator('external.eps.finalize'));
        $this->waitForElement($this->getLocator('external.eps.ok'), 30);
        $this->clickAndWait($this->getLocator('external.eps.ok'));
        $this->waitForElement($this->getLocator('external.eps.goBackToOxid'), 30);
        $this->clickAndWait($this->getLocator('external.eps.goBackToOxid'));
    }
}
