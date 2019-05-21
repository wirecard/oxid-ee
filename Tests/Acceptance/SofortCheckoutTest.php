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
    public function getPaymentMethod()
    {
        return new SofortPaymentMethod();
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
        $this->waitForElement($this->getLocator('external.sofort.country'), 30);
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
        $this->clickAndWait($this->getLocator('external.sofort.nextStep'));
        $this->type(
            $this->getLocator('external.sofort.userId'),
            $this->getConfig('payments.sofort.userId')
        );
        $this->type(
            $this->getLocator('external.sofort.password'),
            $this->getConfig('payments.sofort.password')
        );
        $this->clickAndWait($this->getLocator('external.sofort.nextStep'));
        $this->click($this->getLocator('external.sofort.account'));
        $this->clickAndWait($this->getLocator('external.sofort.nextStep'));
        $this->type(
            $this->getLocator('external.sofort.tan'),
            $this->getConfig('payments.sofort.tan')
        );
        $this->clickAndWait($this->getLocator('external.sofort.nextStep'));
    }
}
