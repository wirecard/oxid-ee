<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend;

use \OxidEsales\Eshop\Application\Model\Article;
use \OxidEsales\Eshop\Application\Model\Basket;
use \OxidEsales\Eshop\Application\Model\State;
use \OxidEsales\Eshop\Application\Model\Order;
use \OxidEsales\Eshop\Application\Model\Payment;
use \OxidEsales\Eshop\Application\Model\User;
use \OxidEsales\Eshop\Core\Registry;
use \OxidEsales\Eshop\Core\Session;

use \Wirecard\Oxid\Core\Helper;
use \Wirecard\Oxid\Core\Payment_Method_Factory;

use \Wirecard\Oxid\Model\Credit_Card_Payment_Method;
use \Wirecard\PaymentSdk\Entity\AccountHolder;
use \Wirecard\PaymentSdk\Entity\Amount;
use \Wirecard\PaymentSdk\Entity\Device;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use \Wirecard\PaymentSdk\Response\Response;
use \Wirecard\PaymentSdk\Response\SuccessResponse;
use \Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use \Wirecard\PaymentSdk\Transaction\Transaction;
use \Wirecard\PaymentSdk\TransactionService;
use \Wirecard\PaymentSdk\Response\FailureResponse;
use \Wirecard\PaymentSdk\Response\InteractionResponse;
use \Wirecard\PaymentSdk\Entity\Redirect;
use \Wirecard\PaymentSdk\Entity\Basket as WdBasket;
use \Wirecard\PaymentSdk\Entity\Item;

/**
 * Class BasePaymentGateway
 *
 * Base class for all payment methods
 *
 * @mixin  \OxidEsales\Eshop\Application\Model\PaymentGateway
 *
 */
class Payment_Gateway extends Payment_Gateway_parent
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $oLogger;

    /**
     * @var \OxidEsales\Eshop\Core\Language
     */
    private $oLang;

    /**
     * BasePaymentGateway constructor.
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function __construct()
    {
        $this->oLogger = Registry::getLogger();
        $this->oLang = Registry::getLang();
    }

    /**
     * Executes payment, returns true on success.
     *
     * @param double                      $dAmount Goods amount
     * @param \Wirecard\Oxid\Extend\Order $oOrder  User ordering object
     *
     * @return Response|FailureResponse|SuccessResponse
     *
     * @override
     *
     * @SuppressWarnings(PHPMD.Coverage)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function executePayment($dAmount, &$oOrder)
    {

        //TODO
//        if (!$oOrder->isWirecardPaymentType()) {
//            return parent::executePayment($dAmount, $oOrder);
//        }

        $oResponse = null;

        try {
            $oResponse = self::makeTransaction($dAmount, $oOrder);
        } catch (\Exception $exc) {
            $this->oLogger->error("Error processing transaction", [$exc]);
            return false;
        }

        if ($oResponse instanceof FailureResponse) {
            $this->oLogger->error('Error processing transaction:');

            foreach ($oResponse->getStatusCollection() as $oStatus) {
                /**
                 * @var $oStatus \Wirecard\PaymentSdk\Entity\Status
                 */
                $sSeverity = ucfirst($oStatus->getSeverity());
                $sCode = $oStatus->getCode();
                $sDescription = $oStatus->getDescription();
                $this->oLogger->error("\t$sSeverity with code $sCode and message '$sDescription' occurred.");
            }
            return false;
        }

        if ($oResponse instanceof FormInteractionResponse) {
            $this->oLogger->error("FormInteractionResponse not handled yet!");
        }

        $sPageUrl = null;
        if ($oResponse instanceof InteractionResponse) {
            $sPageUrl = $oResponse->getRedirectUrl();
            $this->oLogger->debug($sPageUrl);
            Registry::getUtils()->redirect($sPageUrl);
        }

        return true;
    }

    /**
     * Returns country code
     *
     * @param string $countryId
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    private static function _getCountryCode($countryId)
    {
        $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $country->load($countryId);
        return $country->oxcountry__oxisoalpha2->value;
    }

    /**
     * Returns a descriptor
     *
     * @param string $orderId the order ID to get the descriptor from
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function getDescriptor($orderId): string
    {
        $shopId = \OxidEsales\Eshop\Core\Registry::getConfig()->getShopId();
        $shop = oxNew(\OxidEsales\Eshop\Application\Model\Shop::class);
        $shop->load($shopId);
        return $shop->oxshops__oxname->value . " " . $orderId;
    }

    /**
     * Returns a redirect object
     *
     * @param Session $oSession
     *
     * @param string  $sShopUrl
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function getRedirectUrls($oSession, $sShopUrl)
    {
        $sSid = $oSession->sid(true);
        if ($sSid != '') {
            $sSid = '&' . $sSid;
        }

        $sErrorText = $this->oLang->translateString('order_error');
        $oRedirect = new Redirect(
            $sShopUrl . 'index.php?cl=thankyou' . $sSid,
            $sShopUrl . 'index.php?type=cancel&cl=payment',
            $sShopUrl . 'index.php?type=error&cl=payment&errortext=' . urlencode($sErrorText)
        );
        return $oRedirect;
    }

    /**
     * Executes the transaction through EE
     *
     * @param float $dAmount Amount to pay
     *
     * @param Order $oOrder
     *
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     *
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.Coverage)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function makeTransaction($dAmount, $oOrder)
    {
        $sShopUrl = $this->getConfig()->getCurrentShopUrl();
        $oSession = $this->getSession();
        $oShopConfig = $this->getConfig();

        $oRedirect = self::getRedirectUrls($oSession, $sShopUrl);
        $oPaymentMethod = Payment_Method_Factory::create(Credit_Card_Payment_Method::NAME);

        $oPayment = oxNew(Payment::class);
        $oPayment->load($oOrder->getPaymentType()->oxuserpayments__oxpaymentsid->value);

        $oTransactionService = new TransactionService($oPaymentMethod->getConfig($oPayment), $this->oLogger);

        if (!empty($oShopConfig->getRequestParameter('jsresponse'))) {
            return $oTransactionService->processJsResponse($_POST, $sShopUrl . "termUrlInMakeTrnasaction.php");
        }

        $oTransaction = $oPaymentMethod->getTransaction();
        $oTransaction->setRedirect($oRedirect);

        $oCurrency = $oShopConfig->getActShopCurrencyObject();


        //TODO cgrach add back
        //$oTransaction->setAmount(new Amount($dAmount, $oCurrency->name));
        $oTransaction->setAmount(new Amount(0, "EUR"));

        $oBasket = $oSession->getBasket();
        $oUser = $oBasket->getBasketUser();

        $sPaymentId = $oBasket->getPaymentId();
        $oPayment = oxNew(Payment::class);
        $oPayment->load($sPaymentId);

        if ($oPayment->oxpayments__wdoxidee_additional_info->value) {
            $this->_addAdditionalInfo($oTransaction, $oOrder, $oUser, $oPayment);
        }

        if ($oPayment->oxpayments__wdoxidee_descriptor->value) {
            $descriptor = self::getDescriptor($oOrder->oxorder__oxid->value);
            $oTransaction->setDescriptor($descriptor);
        }

        if ($oPayment->oxpayments__wdoxidee_basket->value) {
            $this->_addBasketInfo($oTransaction, $oBasket);
        }

        $oTransaction->setNotificationUrl($sShopUrl . 'notify.php');

        if ($oTransaction instanceof PayPalTransaction) {
            $oTransaction->setOrderDetail(sprintf(
                '%s %s %s',
                $oOrder->oxorder__oxbillemail->value,
                $oUser->oxuser__oxfname->value,
                $oUser->oxuser__oxlname->value
            ));
        }

        //TODO cgrach add back
//        $oResponse = $oTransactionService->process($oTransaction,
// $oPayment->oxpayments__wdoxidee_transactiontype->value);
        $oResponse = $oTransactionService->process($oTransaction, 'reserve');
        return $oResponse;
    }

    /**
     * Add additional info to the transaction
     *
     * @param Transaction $oTransaction
     * @param Order       $oOrder
     * @param User        $oUser
     * @param Payment     $oPayment
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    private function _addAdditionalInfo(
        Transaction &$oTransaction,
        Order $oOrder,
        User $oUser,
        Payment $oPayment
    ) {
    

        $sRemoteAddress = Registry::getUtilsServer()->getRemoteAddress();
        $oTransaction->setIpAddress($sRemoteAddress);

        $oTransaction->setConsumerId($oUser->oxuser__oxid->value);
        $oTransaction->setOrderNumber($oOrder->oxorder__oxid->value);

        $oAccountHolder = $this->_buildAccountHolder($oOrder, $oUser);
        $oTransaction->setAccountHolder($oAccountHolder);

        $sFirstName = $oOrder->oxorder__oxdelfname->value;
        $oTransaction->setShipping($sFirstName ? $this->_buildShipping($oOrder) : $oAccountHolder);

        $oDevice = new Device($_SERVER['HTTP_USER_AGENT']);
        $sMaid = $oPayment->oxpayments__wdoxidee_maid->value;
        $sDeviceId = Helper::createDeviceFingerprint($sMaid, $this->getSession()->getId());
        $oDevice->setFingerprint($sDeviceId);
        $oTransaction->setDevice($oDevice);
    }

    /**
     *
     * Build an account holder
     *
     * @param Order $oOrder
     * @param User  $oUser
     * @return AccountHolder
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    private function _buildAccountHolder(Order $oOrder, User $oUser): AccountHolder
    {
        $oAccountHolder = new \Wirecard\PaymentSdk\Entity\AccountHolder();

        $oAddress = new \Wirecard\PaymentSdk\Entity\Address(
            self::_getCountryCode($oUser->oxuser__oxcountryid),
            $oUser->oxuser__oxcity->value,
            $oUser->oxuser__oxstreet->value . ' ' . $oUser->oxuser__oxstreetnr->value
        );

        $oAddress->setPostalCode($oUser->oxuser__oxzip->value);

        $sState = $oUser->getStateTitle();
        if (!empty($sState)) {
            $oAddress->setState($sState);
        }

        $sStreet2 = $oUser->oxuser__oxaddinfo->value;
        if (!empty($sStreet2)) {
            $oAddress->setStreet2($sStreet2);
        }

        $oAccountHolder->setAddress($oAddress);
        $oAccountHolder->setFirstName($oUser->oxuser__oxfname->value);
        $oAccountHolder->setLastName($oUser->oxuser__oxlname->value);
        $oAccountHolder->setPhone($oUser->oxuser__oxfon->value);
        $oAccountHolder->setEmail($oOrder->oxorder__oxbillemail->value);

        return $oAccountHolder;
    }

    /**
     *
     * Build the shipping account holder
     *
     * @param Order $oOrder
     * @return AccountHolder
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    private function _buildShipping(Order $oOrder): AccountHolder
    {
        $oAddress = new \Wirecard\PaymentSdk\Entity\Address(
            self::_getCountryCode($oOrder->oxorder__oxdelcountryid),
            $oOrder->oxorder__oxdelcity->value,
            $oOrder->oxorder__oxdelstreet->value . ' ' . $oOrder->oxorder__oxdelstreetnr->value
        );
        $oAddress->setPostalCode($oOrder->oxorder__oxdelzip->value);

        $sStateId = $oOrder->oxOrder__oxdelstateid->value;
        if (!empty($sStateId)) {
            $oState = oxNew(State::class);
            $oAddress->setState($oState->getTitleById($sStateId));
        }

        $sStreet2 = $oOrder->oxOrder__oxdeladdinfo->value;
        if (!empty($sStreet2)) {
            $oAddress->setStreet2($sStreet2);
        }

        $oAccount = new \Wirecard\PaymentSdk\Entity\AccountHolder();
        $oAccount->setAddress($oAddress);
        $oAccount->setFirstName($oOrder->oxorder__oxdelfname->value);
        $oAccount->setLastName($oOrder->oxorder__oxdellname->value);
        $oAccount->setPhone($oOrder->oxorder__oxdelfon->value);

        return $oAccount;
    }

    /**
     *
     * Add the basket info to transaction
     *
     * @param Transaction $oTransaction
     * @param Basket      $oBasket
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    private function _addBasketInfo(Transaction &$oTransaction, Basket $oBasket)
    {
        $finalPrices = array();
        $contents = $oBasket->getContents();
        foreach ($contents as $content) {
            $finalPrices[$content->getProductId()] = $content->getFUnitPrice();
        }

        $oWdBasket = new WdBasket;
        $oWdBasket->setVersion($oTransaction);
        $oArticles = $oBasket->getBasketSummary()->aArticles;
        $oCurrency = $this->getConfig()->getActShopCurrencyObject();

        foreach ($oArticles as $key => $value) {
            $this->_addItemToBasket($oWdBasket, $key, $value, $finalPrices, $oCurrency);
        }
        if ($oBasket->getDeliveryCosts()) {
            $item = new Item(
                "Shipping",
                new Amount($oBasket->getDeliveryCosts(), $oCurrency->name),
                1
            );
            $item->setTaxRate($oBasket->getDelCostVatPercent());
            $item->setTaxAmount(new Amount($oBasket->getDeliveryCost()->getVatValue(), $oCurrency->name));

            $oWdBasket->add($item);
        }
        $oTransaction->setBasket($oWdBasket);
    }

    /**
     * Adds an article to the basket
     *
     * @param WdBasket $oBasket
     * @param string   $sArticleKey
     * @param int      $iQuantity
     * @param array    $aPrices
     * @param Currency $oCurrency
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    private function _addItemToBasket(
        WdBasket &$oBasket,
        string $sArticleKey,
        int $iQuantity,
        array $aPrices,
        $oCurrency
    ) {
    

        $oArticle = oxNew(Article::class);
        $oArticle->load($sArticleKey);
        $item = new Item(
            $oArticle->oxarticles__oxtitle->value,
            new Amount($aPrices[$sArticleKey], $oCurrency->name),
            $iQuantity
        );
        $item->setTaxRate(floatval($oArticle->getPrice()->getVat()));
        $item->setTaxAmount(new Amount($oArticle->getPrice()->getVatValue(), $oCurrency->name));
        $oBasket->add($item);
    }
}
