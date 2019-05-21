<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

use Wirecard\Oxid\Model\SepaDirectDebitPaymentMethod;

/**
 * Acceptance tests for the SEPA Direct Debit checkout flow.
 */
class SepaDirectDebitCheckoutTest extends CheckoutTestCase
{
    public function getPaymentMethod()
    {
        return new SepaDirectDebitPaymentMethod();
    }

    public function testCheckoutForPurchase()
    {
        $this->setPaymentActionPurchase();
        $this->goThroughCheckout();
        $this->waitForRedirectConfirmation();

        $this->assertPaymentSuccessful();
    }

    public function testCheckoutForAuthorize()
    {
        $this->setPaymentActionAuthorize();
        $this->goThroughCheckout();
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
        $this->type(
            $this->getLocator('external.sepadd.accountHolder'),
            $this->getConfig('payments.sepadd.accountHolder')
        );
        $this->type(
            $this->getLocator('external.sepadd.iban'),
            $this->getConfig('payments.sepadd.iban')
        );
        $this->continueToNextStep();

        // Step 4: Order
        $this->continueToNextStep(0);

        // Step 4: Popover
        $this->click($this->getLocator('external.sepadd.terms'));
        $this->clickAndWait($this->getLocator('external.sepadd.nextStep'));
    }
}
