<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

use Wirecard\Oxid\Model\PaymentMethod\RatepayInvoicePaymentMethod;

/**
 * Acceptance tests for the Ratepay Invoice checkout flow.
 */
class RatepayInvoiceCheckoutTest extends CheckoutTestCase
{
    public function getPaymentMethodName()
    {
        return RatepayInvoicePaymentMethod::getName(true);
    }

    public function testCheckoutWithRequiredProfileData()
    {
        parent::goThroughCheckout();
        $this->waitForRedirectConfirmation();

        $this->assertPaymentSuccessful();
    }

    public function testCheckoutWithoutRequiredProfileData()
    {
        $this->removeRequiredProfileData();
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
            $this->paymentMethod::getName()
        ));
        $this->type(
            $this->getLocator('external.ratepayInvoice.dateOfBirth'),
            $this->getConfig('payments.ratepayInvoice.dateOfBirth')
        );
        $this->type(
            $this->getLocator('external.ratepayInvoice.phone'),
            $this->getConfig('payments.ratepayInvoice.phone')
        );
        $this->continueToNextStep();

        // Step 4: Order
        $this->continueToNextStep(self::WAIT_TIME_EXTERNAL);
    }

    private function removeRequiredProfileData()
    {
        $this->executeSql("UPDATE `oxuser` SET `OXFON` = '', `OXBIRTHDATE` = '0000-00-00'
            WHERE `OXID` = '{$this->getMockData('oxuser.0.OXID')}';");
    }
}
