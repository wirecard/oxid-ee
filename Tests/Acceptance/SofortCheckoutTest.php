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
    public function getPaymentMethodName()
    {
        return SofortPaymentMethod::getName(true);
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
        $this->waitForElement($this->getLocator('external.sofort.country'), self::WAIT_TIME_EXTERNAL);
        $this->select(
            $this->getLocator('external.sofort.country'),
            $this->getConfig('payments.sofort.country')
        );
        $this->waitForPageToLoad();
        $this->type(
            $this->getLocator('external.sofort.bank'),
            $this->getConfig('payments.sofort.bank')
        );
        $this->fireEvent($this->getLocator('external.sofort.bank'), 'input');
        $this->click($this->getLocator('external.sofort.nextStep'));
        $this->waitForElement($this->getLocator('external.sofort.userId'), self::WAIT_TIME_EXTERNAL);
        $this->type(
            $this->getLocator('external.sofort.userId'),
            $this->getConfig('payments.sofort.userId')
        );
        $this->type(
            $this->getLocator('external.sofort.password'),
            $this->getConfig('payments.sofort.password')
        );
        $this->click($this->getLocator('external.sofort.nextStep'));
        $this->waitForElement($this->getLocator('external.sofort.account'), self::WAIT_TIME_EXTERNAL);
        $this->click($this->getLocator('external.sofort.account'));
        $this->click($this->getLocator('external.sofort.nextStep'));
        $this->waitForElement($this->getLocator('external.sofort.tan'), self::WAIT_TIME_EXTERNAL);
        $this->type(
            $this->getLocator('external.sofort.tan'),
            $this->getConfig('payments.sofort.tan')
        );
        $this->click($this->getLocator('external.sofort.nextStep'));
    }
}
