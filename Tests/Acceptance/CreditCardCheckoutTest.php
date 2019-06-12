<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

use Wirecard\Oxid\Model\CreditCardPaymentMethod;

/**
 * Acceptance tests for the Credit Card checkout flow.
 */
class CreditCardCheckoutTest extends CheckoutTestCase
{
    public function getPaymentMethodName()
    {
        return CreditCardPaymentMethod::getName(true);
    }

    public function testCheckoutForPurchaseNonThreeD()
    {
        $this->setPaymentActionPurchase();
        $this->forceNonThreeD();
        $this->goThroughCheckout();

        $this->assertPaymentSuccessful();
    }

    public function testCheckoutForAuthorizeNonThreeD()
    {
        $this->setPaymentActionAuthorize();
        $this->forceNonThreeD();
        $this->goThroughCheckout();

        $this->assertPaymentSuccessful();
    }

    public function testCheckoutForPurchaseThreeD()
    {
        $this->setPaymentActionPurchase();
        $this->forceThreeD();
        $this->goThroughCheckout();
        $this->goThroughExternalFlow();
        $this->waitForRedirectConfirmation();

        $this->assertPaymentSuccessful();
    }

    public function testCheckoutForAuthorizeThreeD()
    {
        $this->setPaymentActionAuthorize();
        $this->forceThreeD();
        $this->goThroughCheckout();
        $this->goThroughExternalFlow();
        $this->waitForRedirectConfirmation();

        $this->assertPaymentSuccessful();
    }

    private function forceThreeD()
    {
        $this->executeSql("UPDATE `oxpayments`
            SET `WDOXIDEE_MAID` = '', `WDOXIDEE_SECRET` = ''
            WHERE `OXID` = '{$this->getPaymentMethodName()}'");
    }

    private function forceNonThreeD()
    {
        $this->executeSql("UPDATE `oxpayments`
            SET `WDOXIDEE_THREE_D_MAID` = '', `WDOXIDEE_THREE_D_SECRET` = ''
            WHERE `OXID` = '{$this->getPaymentMethodName()}'");
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
        $this->continueToNextStep();

        // Step 4: Order
        $this->waitForItemAppear($this->getLocator('external.creditcard.frame'), self::WAIT_TIME_EXTERNAL);
        $this->selectFrameBySelector($this->getLocator('external.creditcard.frame'));
        $this->type(
            $this->getLocator('external.creditcard.firstName'),
            $this->getConfig('payments.creditcard.firstName')
        );
        $this->type(
            $this->getLocator('external.creditcard.lastName'),
            $this->getConfig('payments.creditcard.lastName')
        );
        $this->type(
            $this->getLocator('external.creditcard.cardNumber'),
            $this->getConfig('payments.creditcard.cardNumber')
        );
        $this->fireEvent($this->getLocator('external.creditcard.cardNumber'), 'keyup');
        $this->type(
            $this->getLocator('external.creditcard.cvv'),
            $this->getConfig('payments.creditcard.cvv')
        );
        $this->select(
            $this->getLocator('external.creditcard.expiryMonth'),
            $this->getConfig('payments.creditcard.expiryMonth')
        );
        $this->select(
            $this->getLocator('external.creditcard.expiryYear'),
            $this->getConfig('payments.creditcard.expiryYear')
        );
        $this->selectWindow(null);
        $this->continueToNextStep();
    }

    private function goThroughExternalFlow()
    {
        $this->waitForElement($this->getLocator('external.creditcard.password'), self::WAIT_TIME_EXTERNAL);
        $this->type(
            $this->getLocator('external.creditcard.password'),
            $this->getConfig('payments.creditcard.password')
        );
        $this->click($this->getLocator('external.creditcard.continueButton'));
    }
}
