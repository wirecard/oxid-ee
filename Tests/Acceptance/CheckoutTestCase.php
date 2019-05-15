<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

/**
 * Acceptance test class for testing checkout flows.
 */
class CheckoutTestCase extends BaseAcceptanceTestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->activateModulePaymentMethods();
        $this->addMockData();
    }

    /**
     * Activates all module payment methods.
     */
    public function activateModulePaymentMethods()
    {
        $this->executeSql("UPDATE `oxpayments` SET `OXACTIVE` = '1' WHERE `OXID` LIKE 'wd%'");
    }

    /**
     * Adds mock data required to complete a checkout.
     */
    public function addMockData()
    {
        $this->executeSql("INSERT INTO `oxuser` (`OXID`, `OXRIGHTS`, `OXUSERNAME`, `OXPASSWORD`, `OXPASSSALT`, `OXFNAME`, `OXLNAME`, `OXSTREET`, `OXSTREETNR`, `OXCITY`, `OXCOUNTRYID`, `OXZIP`, `OXSAL`) VALUES ('wdcheckoutuser', 'user', 'payment@test.com', 'd04c7c05808811484a38486479ebecd5776bdf76966db23b6a7469d6f0724af5fcb7bb3f77de6372435567951dbc1b8eda29521bcc6b5ccbe778af60847c7825', 'a022994047f11859e9430ec3b37d977d', 'Payment', 'Test', 'Tester Street', '1', 'Berlin', 'a7c40f631fc920687.20179984', '10115', 'MR')");
        $this->executeSql("INSERT INTO `oxarticles` (`OXID`, `OXARTNUM`, `OXTITLE_1`, `OXPRICE`, `OXSTOCK`) VALUES ('wdcheckoutarticle', '1337', 'Test Article', '100.99', '10')");
    }

    /**
     * Logs the mock user into the frontend.
     */
    public function loginMockUserToFrontend()
    {
        $this->loginInFrontend('payment@test.com', 'payment');
    }

    /**
     * Adds the mock article to the basket.
     */
    public function addMockArticleToBasket()
    {
        $this->addToBasket('wdcheckoutarticle');
    }

    /**
     * Navigates to the next step in the checkout.
     */
    public function continueToNextStep()
    {
        $this->clickAndWait($this->getLocator('checkout.nextStep'));
    }

    /**
     * Waits until the redirect process has finished.
     * @param int $seconds
     */
    public function waitForRedirectConfirmation($seconds = 30)
    {
        // there might be an insecure certificate warning that needs to be dismissed
        $this->getMinkSession()->getDriver()->getBrowser()->keyPressNative('10');

        if ($seconds > 0 && strpos($this->getLocation(), shopURL) !== 0) {
            sleep(1);
            $this->waitForRedirectConfirmation($seconds - 1);
        }
    }

    /**
     * Checks if the given URL is pointing to the "Thank you" page.
     * @param string $url
     * @return bool
     */
    public function isThankYouPage($url)
    {
        return strpos($url, $this->_getShopUrl([
            'cl' => 'thankyou',
        ])) === 0;
    }
}
