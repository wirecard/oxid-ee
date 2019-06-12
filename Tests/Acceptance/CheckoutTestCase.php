<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

use Wirecard\Oxid\Model\Transaction;

/**
 * Acceptance test class for testing checkout flows.
 */
abstract class CheckoutTestCase extends BaseAcceptanceTestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->insertMockData();
        $this->activatePaymentMethod();
    }

    /**
     * Payment method name getter.
     */
    abstract public function getPaymentMethodName();

    /**
     * Activates the payment method.
     */
    public function activatePaymentMethod()
    {
        $this->executeSql("UPDATE `oxpayments` SET `OXACTIVE` = '1'
            WHERE `OXID` = '{$this->getPaymentMethodName()}'");
    }

    /**
     * Sets a payment method's payment action to "purchase".
     */
    public function setPaymentActionPurchase()
    {
        $this->executeSql("UPDATE `oxpayments` SET `WDOXIDEE_TRANSACTIONACTION` = '" . Transaction::ACTION_PAY .
            "' WHERE `OXID` = '{$this->getPaymentMethodName()}'");
    }

    /**
     * Sets a payment method's payment action to "authorize".
     */
    public function setPaymentActionAuthorize()
    {
        $this->executeSql("UPDATE `oxpayments` SET `WDOXIDEE_TRANSACTIONACTION` = '" . Transaction::ACTION_RESERVE .
            "' WHERE `OXID` = '{$this->getPaymentMethodName()}'");
    }

    /**
     * Inserts mock data to the database.
     */
    public function insertMockData()
    {
        foreach ($this->getMockData() as $table => $entries) {
            foreach ($entries as $fields) {
                $columns = '`' . implode('`, `', array_keys($fields)) . '`';
                $valuesInsert = '\'' . implode('\', \'', array_values($fields)) . '\'';
                $valuesUpdate = implode(', ', array_map(function ($column) {
                    return "`{$column}`=VALUES(`{$column}`)";
                }, array_keys($fields)));

                $this->executeSql("INSERT INTO `{$table}` ({$columns}) VALUES ({$valuesInsert}) ON DUPLICATE KEY UPDATE {$valuesUpdate}");
            }
        }
    }

    /**
     * Logs a mock user into the frontend.
     */
    public function loginMockUserToFrontend()
    {
        $userName = $this->getMockData('oxuser.0.OXUSERNAME');
        $password = $userName; // username and password are equal for mock users

        $this->loginInFrontend($userName, $password);
    }

    /**
     * Adds a mock article to the basket.
     */
    public function addMockArticleToBasket()
    {
        $this->addToBasket($this->getMockData('oxarticles.0.OXID'));
    }

    /**
     * Navigates to the next step in the checkout.
     */
    public function continueToNextStep($seconds = 10)
    {
        $this->clickAndWait($this->getLocator('checkout.nextStep'), $seconds);
    }

    /**
     * Navigates through the checkout process.
     */
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
        $this->continueToNextStep();
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
        $queryString = parse_url($url, PHP_URL_QUERY);

        return strpos($url, $this->_getShopUrl()) === 0 &&
            (!$queryString || strpos($queryString, 'cl=thankyou') !== false);
    }

    /**
     * Asserts that, based on the current page, a payment was successful.
     */
    public function assertPaymentSuccessful()
    {
        $this->assertTrue($this->isThankYouPage($this->getLocation()), 'Payment was not successful.');
    }
}
