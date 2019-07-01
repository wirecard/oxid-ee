<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

use Wirecard\Oxid\Model\MasterpassPaymentMethod;

/**
 * Acceptance tests for the Masterpass checkout flow.
 */
class MasterpassCheckoutTest extends CheckoutTestCase
{
    public function getPaymentMethodName()
    {
        return MasterpassPaymentMethod::getName(true);
    }

    public function testCheckout()
    {
        $this->goThroughCheckout();
        $this->goThroughExternalFlow();

        $this->waitForRedirectConfirmation(self::WAIT_TIME_EXTERNAL);

        $this->assertPaymentSuccessful();
    }

    private function goThroughExternalFlow()
    {

        $this->waitForElement($this->getLocator('external.masterpass.username'), self::WAIT_TIME_EXTERNAL * 3);
        $this->type(
            $this->getLocator('external.masterpass.username'),
            $this->getConfig('payments.masterpass.username')
        );
        $this->clickAndWait($this->getLocator('external.masterpass.signIn'), self::WAIT_TIME_EXTERNAL);

        $this->waitForElement($this->getLocator('external.masterpass.continueToWallet'), self::WAIT_TIME_EXTERNAL * 2);
        $this->clickAndWait($this->getLocator('external.masterpass.continueToWallet'), self::WAIT_TIME_EXTERNAL);

        $this->waitForElement($this->getLocator('external.masterpass.password'), self::WAIT_TIME_EXTERNAL * 2);
        $this->type(
            $this->getLocator('external.masterpass.password'),
            $this->getConfig('payments.masterpass.password')
        );

        $this->clickAndWait($this->getLocator('external.masterpass.signIn'), self::WAIT_TIME_EXTERNAL);
        $this->waitForElement($this->getLocator('external.masterpass.continue'), self::WAIT_TIME_EXTERNAL);
        $this->clickAndWait($this->getLocator('external.masterpass.continue'), self::WAIT_TIME_EXTERNAL);
    }
}
