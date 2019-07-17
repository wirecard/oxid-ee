<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

use Wirecard\Oxid\Model\PaymentMethod\PayolutionBtwobPaymentMethod;

class PayolutionBtowbCheckoutTest extends CheckoutTestCase
{

    /**
     * @inheritdoc
     *
     * @return string
     *
     */
    public function getPaymentMethodName()
    {
        return PayolutionBtwobPaymentMethod::getName();
    }

    public function testCheckoutWithRequiredProfileData()
    {
        $this->goThroughCheckoutWithRequiredProfileData();
        $this->waitForRedirectConfirmation();

        $this->assertPaymentSuccessful();
    }

    public function testCheckoutWithoutRequiredProfileData()
    {
        $this->removeRequiredProfileData();
        $this->goThroughCheckoutWithoutRequiredProfileData();
        $this->waitForRedirectConfirmation();

        $this->assertPaymentSuccessful();
    }

    private function goThroughCheckoutWithRequiredProfileData()
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

        $this->click($this->getLocator('external.payolutionB2B.termsCheckbox'));

        $this->continueToNextStep();
    }

    private function removeRequiredProfileData()
    {
        $this->executeSql("UPDATE `oxuser` SET `OXCOMPANY` = ''
            WHERE `OXID` = '{$this->getMockData('oxuser.0.OXID')}';");
    }

    private function goThroughCheckoutWithoutRequiredProfileData()
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

        $this->type(
            $this->getLocator('external.payolutionB2B.companyName'),
            $this->getConfig('payments.payolutionB2B.companyName')
        );

        $this->continueToNextStep();

        $this->click($this->getLocator('external.payolutionB2B.termsCheckbox'));

        $this->continueToNextStep();
    }
}
