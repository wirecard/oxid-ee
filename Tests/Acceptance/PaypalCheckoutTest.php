<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

use Wirecard\Oxid\Model\PaymentMethod\PaypalPaymentMethod;

/**
 * Acceptance tests for the PayPal checkout flow.
 */
class PaypalCheckoutTest extends CheckoutTestCase
{
    /**
     * Catch possible A/B tests
     *
     * @inheritdoc
     */
    protected $retryTimes = 1;

    public function getPaymentMethodName()
    {
        return PaypalPaymentMethod::getName();
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

    private function goThroughExternalFlow()
    {
        $this->waitForElement($this->getLocator('external.paypal.email'), self::WAIT_TIME_EXTERNAL);
        $this->type(
            $this->getLocator('external.paypal.email'),
            $this->getConfig('payments.paypal.email')
        );
        $this->click($this->getLocator('external.paypal.login'));
        $this->waitForItemAppear($this->getLocator('external.paypal.password'), self::WAIT_TIME_EXTERNAL);
        $this->type(
            $this->getLocator('external.paypal.password'),
            $this->getConfig('payments.paypal.password')
        );
        $this->click($this->getLocator('external.paypal.login'));
        $this->waitForElement($this->getLocator('external.paypal.nextStep'), self::WAIT_TIME_EXTERNAL);
        $this->clickAndWait($this->getLocator('external.paypal.nextStep'), 3);

        // there might be a confirmation step here
        if ($this->isElementPresent($this->getLocator('external.paypal.nextStep'))) {
            $this->click($this->getLocator('external.paypal.nextStep'));
        }
    }
}
