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
     * @var integer
     */
    const WAIT_TIME_INTERNAL = 20;

    /**
     * @var integer
     */
    const WAIT_TIME_EXTERNAL = 40;

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
        foreach ($this->getMockData() as $sTableName => $aEntries) {
            foreach ($aEntries as $oFields) {
                $sColumns = '`' . implode('`, `', array_keys($oFields)) . '`';
                $sValuesInsert = '\'' . implode('\', \'', array_values($oFields)) . '\'';
                $sValuesUpdate = implode(', ', array_map(function ($sColumn) {
                    return "`{$sColumn}`=VALUES(`{$sColumn}`)";
                }, array_keys($oFields)));

                $this->executeSql("INSERT INTO `{$sTableName}` ({$sColumns}) VALUES ({$sValuesInsert}) ON DUPLICATE KEY UPDATE {$sValuesUpdate}");
            }
        }
    }

    /**
     * Logs a mock user into the frontend.
     */
    public function loginMockUserToFrontend()
    {
        $sUserName = $this->getMockData('oxuser.0.OXUSERNAME');
        $sPassword = $sUserName; // username and password are equal for mock users

        $this->loginInFrontend($sUserName, $sPassword);
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
    public function continueToNextStep($iSeconds = self::WAIT_TIME_INTERNAL)
    {
        $this->clickAndWait($this->getLocator('checkout.nextStep'), $iSeconds);
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
     *
     * @param int $iSeconds
     */
    public function waitForRedirectConfirmation($iSeconds = self::WAIT_TIME_EXTERNAL)
    {
        // there might be an insecure certificate warning that needs to be dismissed
        $this->getMinkSession()->getDriver()->getBrowser()->keyPressNative('10');

        if ($iSeconds > 0 && strpos($this->getLocation(), shopURL) !== 0) {
            sleep(1);
            $this->waitForRedirectConfirmation($iSeconds - 1);
        }
    }

    /**
     * Checks if the given URL is pointing to the "Thank you" page.
     *
     * @param string $sUrl
     *
     * @return bool
     */
    public function isThankYouPage($sUrl)
    {
        $sQueryString = parse_url($sUrl, PHP_URL_QUERY);

        return strpos($sUrl, $this->_getShopUrl()) === 0 &&
            (!$sQueryString || strpos($sQueryString, 'cl=thankyou') !== false);
    }

    /**
     * Asserts that, based on the current page, a payment was successful.
     */
    public function assertPaymentSuccessful()
    {
        $this->assertTrue($this->isThankYouPage($this->getLocation()), 'Payment was not successful.');
    }
}
