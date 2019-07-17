<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

use Wirecard\Oxid\Model\PaymentMethod\SepaDirectDebitPaymentMethod;

/**
 * Acceptance tests for the SEPA Direct Debit checkout flow.
 */
class SepaDirectDebitCheckoutTest extends CheckoutTestCase
{
    public function getPaymentMethodName()
    {
        return SepaDirectDebitPaymentMethod::getName(true);
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

    public function testCheckoutForPurchaseWithBic()
    {
        $this->setPaymentActionPurchase();
        $this->enableBic();
        $this->goThroughCheckout();
        $this->waitForRedirectConfirmation();

        $this->assertPaymentSuccessful();
    }

    public function testCheckoutForAuthorizeWithBic()
    {
        $this->setPaymentActionAuthorize();
        $this->enableBic();
        $this->goThroughCheckout();
        $this->waitForRedirectConfirmation();

        $this->assertPaymentSuccessful();
    }

    private function enableBic()
    {
        $this->executeSql("UPDATE `oxpayments`
            SET `WDOXIDEE_BIC` = '1'
            WHERE `OXID` = '{$this->paymentMethod::getName()}'");
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
            $this->getLocator('external.sepadd.accountHolder'),
            $this->getConfig('payments.sepadd.accountHolder')
        );
        $this->type(
            $this->getLocator('external.sepadd.iban'),
            $this->getConfig('payments.sepadd.iban')
        );

        if ($this->isElementPresent($this->getLocator('external.sepadd.bic'))) {
            $this->type(
                $this->getLocator('external.sepadd.bic'),
                $this->getConfig('payments.sepadd.bic')
            );
        }

        $this->continueToNextStep();

        // Step 4: Order
        $this->continueToNextStep(0);

        // Step 4: Popover
        $this->click($this->getLocator('external.sepadd.terms'));
        $this->clickAndWait($this->getLocator('external.sepadd.nextStep'), self::WAIT_TIME_EXTERNAL);
    }
}
