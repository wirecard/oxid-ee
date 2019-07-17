<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

use Wirecard\Oxid\Model\PaymentMethod\EpsPaymentMethod;

/**
 * Acceptance tests for the eps checkout flow.
 */
class EpsCheckoutTest extends CheckoutTestCase
{
    public function getPaymentMethodName()
    {
        return EpsPaymentMethod::getName();
    }

    public function testCheckout()
    {
        $this->goThroughCheckout();
        $this->goThroughExternalFlow();
        $this->waitForRedirectConfirmation();

        $this->assertPaymentSuccessful();
    }

    private function goThroughExternalFlow()
    {
        $this->waitForElement($this->getLocator('external.eps.bic'), self::WAIT_TIME_EXTERNAL);
        $this->type(
            $this->getLocator('external.eps.bic'),
            $this->getConfig('payments.eps.bic')
        );
        $this->clickAndWait($this->getLocator('external.eps.submitBic'), self::WAIT_TIME_EXTERNAL);
        $this->type(
            $this->getLocator('external.eps.id'),
            $this->getConfig('payments.eps.id')
        );
        $this->clickAndWait($this->getLocator('external.eps.submitLogin'), self::WAIT_TIME_EXTERNAL);
        $this->clickAndWait($this->getLocator('external.eps.submitSign'), self::WAIT_TIME_EXTERNAL);
        $this->clickAndWait($this->getLocator('external.eps.submitFinalize'), self::WAIT_TIME_EXTERNAL);
        $this->clickAndWait($this->getLocator('external.eps.submitConfirm'), self::WAIT_TIME_EXTERNAL);
        $this->click($this->getLocator('external.eps.backToShop'));
    }
}
