<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

use Wirecard\Oxid\Model\PaymentMethod\GiropayPaymentMethod;

/**
 * Acceptance tests for the giropay checkout flow.
 */
class GiropayCheckoutTest extends CheckoutTestCase
{
    public function getPaymentMethodName()
    {
        return GiropayPaymentMethod::getName(true);
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
            $this->paymentMethod::getName()
        ));
        $this->type(
            $this->getLocator('external.giropay.bic'),
            $this->getConfig('payments.giropay.bic')
        );
        $this->continueToNextStep();

        // Step 4: Order
        $this->continueToNextStep();
    }

    private function goThroughExternalFlow()
    {
        $this->waitForElement($this->getLocator('external.giropay.sc'), self::WAIT_TIME_EXTERNAL);
        $this->type(
            $this->getLocator('external.giropay.sc'),
            $this->getConfig('payments.giropay.sc')
        );
        $this->type(
            $this->getLocator('external.giropay.extensionSc'),
            $this->getConfig('payments.giropay.extensionSc')
        );
        $this->click($this->getLocator('external.giropay.nextStep'));
    }
}
