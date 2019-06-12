<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

use Wirecard\Oxid\Model\IdealPaymentMethod;

/**
 * Acceptance tests for the iDEAL checkout flow.
 */
class IdealCheckoutTest extends CheckoutTestCase
{
    public function getPaymentMethodName()
    {
        return IdealPaymentMethod::getName(true);
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
            $this->getPaymentMethodName()
        ));
        $this->select(
            $this->getLocator('external.ideal.bank'),
            $this->getConfig('payments.ideal.bank')
        );
        $this->continueToNextStep();

        // Step 4: Order
        $this->continueToNextStep();
    }

    private function goThroughExternalFlow()
    {
        $this->waitForElement($this->getLocator('external.ideal.nextStep'), 30);
        $this->clickAndWait($this->getLocator('external.ideal.nextStep'));
    }
}
