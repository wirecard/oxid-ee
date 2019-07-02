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

        $this->assertTrue($this->isMasterpassRedirectPage($this->getLocation()), 'Payment was not successful.');
    }

    private function goThroughExternalFlow()
    {
        $this->waitForElement($this->getLocator('external.masterpass.form'), self::WAIT_TIME_EXTERNAL);
    }
}
