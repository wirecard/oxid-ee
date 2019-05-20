<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

use Wirecard\Oxid\Model\SofortPaymentMethod;

/**
 * Acceptance tests for the Sofort. checkout flow.
 */
class SofortCheckoutTest extends CheckoutTestCase
{
    public function getPaymentMethod()
    {
        return new SofortPaymentMethod();
    }

    public function testCheckout()
    {
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
        $this->click($this->getLocator('checkout.paymentMethods.sofort'));
        $this->continueToNextStep();

        // Step 4
        $this->continueToNextStep();
    }

    private function goThroughExternalFlow()
    {
        $this->waitForElement($this->getLocator('external.sofort.country'), 30);
        $this->select(
            $this->getLocator('external.sofort.country'),
            $this->getConfigValue('payments.sofort.country')
        );
        $this->type(
            $this->getLocator('external.sofort.bank'),
            $this->getConfigValue('payments.sofort.bank')
        );
        $this->keyUp($this->getLocator('external.sofort.bank'), ' '); // forces validation
        $this->clickAndWait($this->getLocator('external.sofort.nextStep'));
        $this->type(
            $this->getLocator('external.sofort.userId'),
            $this->getConfigValue('payments.sofort.userId')
        );
        $this->type(
            $this->getLocator('external.sofort.password'),
            $this->getConfigValue('payments.sofort.password')
        );
        $this->clickAndWait($this->getLocator('external.sofort.nextStep'));
        $this->click($this->getLocator('external.sofort.account'));
        $this->clickAndWait($this->getLocator('external.sofort.nextStep'));
        $this->type(
            $this->getLocator('external.sofort.tan'),
            $this->getConfigValue('payments.sofort.tan')
        );
        $this->clickAndWait($this->getLocator('external.sofort.nextStep'));
    }
}
