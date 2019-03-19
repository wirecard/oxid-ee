<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend;

use \OxidEsales\Eshop\Application\Model\Shop;
use \OxidEsales\Eshop\Application\Model\Article;
use \OxidEsales\Eshop\Application\Model\Country;
use \OxidEsales\Eshop\Application\Model\Basket;
use \OxidEsales\Eshop\Application\Model\State;
use \OxidEsales\Eshop\Application\Model\Payment;
use \OxidEsales\Eshop\Application\Model\User;
use \OxidEsales\Eshop\Core\Registry;
use \OxidEsales\Eshop\Core\Session;
use \OxidEsales\Eshop\Core\Field;
use \OxidEsales\Eshop\Core\Language;

use \Wirecard\PaymentSdk\Entity\AccountHolder;
use \Wirecard\PaymentSdk\Entity\Amount;
use \Wirecard\PaymentSdk\Entity\Device;
use \Wirecard\PaymentSdk\Response\Response;
use \Wirecard\PaymentSdk\Response\SuccessResponse;
use \Wirecard\PaymentSdk\Transaction\Transaction;
use \Wirecard\PaymentSdk\TransactionService;
use \Wirecard\PaymentSdk\Response\FailureResponse;
use \Wirecard\PaymentSdk\Response\InteractionResponse;
use \Wirecard\PaymentSdk\Entity\Redirect;
use \Wirecard\PaymentSdk\Entity\Basket as WdBasket;
use \Wirecard\PaymentSdk\Entity\Item;
use \Wirecard\PaymentSdk\Entity\Address;
use \Wirecard\PaymentSdk\Entity\Status;

use \Wirecard\Oxid\Core\Payment_Method_Factory;
use \Wirecard\Oxid\Model\Payment_Method;
use \Wirecard\Oxid\Extend\Order;
use \Wirecard\Oxid\Core\Helper;

use \Psr\Log\LoggerInterface;

/**
 * Class BasePaymentGateway
 *
 * Base class for all payment methods
 *
 * @mixin \OxidEsales\Eshop\Application\Model\PaymentGateway
 *
 */
class Payment_Gateway extends Payment_Gateway_parent
{
    /**
     * @var LoggerInterface
     */
    private $_oLogger;

    /**
     * @var Language
     */
    private $_oLang;

    /**
     * BasePaymentGateway constructor.
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function __construct()
    {
        $this->_oLogger = Registry::getLogger();
        $this->_oLang = Registry::getLang();
    }

    /**
     * Executes payment, returns true on success.
     *
     * @param float $fAmount Goods amount
     * @param Order $oOrder  User ordering object
     *
     * @return Response|FailureResponse|SuccessResponse
     *
     * @override
     *
     * @SuppressWarnings(PHPMD.Coverage)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function executePayment(float $fAmount, Order &$oOrder): bool
    {
        if (!$oOrder->isModulePaymentType()) {
            return parent::executePayment($fAmount, $oOrder);
        }

        try {
            $oResponse = self::_makeTransaction($fAmount, $oOrder);
        } catch (\Exception $exc) {
            $this->_oLogger->error("Error processing transaction", [$exc]);
            return false;
        }

        if ($oResponse instanceof FailureResponse) {
            $this->_oLogger->error('Error processing transaction:');

            foreach ($oResponse->getStatusCollection() as $oStatus) {
                /**
                 * @var Status $oStatus
                 */
                $sSeverity = ucfirst($oStatus->getSeverity());
                $sCode = $oStatus->getCode();
                $sDescription = $oStatus->getDescription();
                $this->_oLogger->error("\t$sSeverity with code $sCode and message '$sDescription' occurred.");
            }
            return false;
        }
        $sPageUrl = null;
        if ($oResponse instanceof InteractionResponse) {
            $sPageUrl = $oResponse->getRedirectUrl();
        }

        Registry::getUtils()->redirect($sPageUrl);
        return true;
    }

    /**
     * Returns country code
     *
     * @param string $sCountryId
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    private function _getCountryCode(string $sCountryId): string
    {
        $country = oxNew(Country::class);
        $country->load($sCountryId);
        return $country->oxcountry__oxisoalpha2->value;
    }

    /**
     * Returns a descriptor
     *
     * @param string $sOrderId the order ID to get the descriptor from
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function getDescriptor(string $sOrderId): string
    {
        $shopId = Registry::getConfig()->getShopId();
        $shop = oxNew(Shop::class);
        $shop->load($shopId);
        return $shop->oxshops__oxname->value . " " . $sOrderId;
    }

    /**
     * Returns a redirect object
     *
     * @param Session $oSession
     * @param string  $sShopUrl
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function getRedirectUrls(Session $oSession, string $sShopUrl): Redirect
    {
        $sSid = $oSession->sid(true);
        if ($sSid != '') {
            $sSid = '&' . $sSid;
        }

        $sErrorText = $this->_oLang->translateString('order_error');
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
     * @param float                       $fAmount
     * @param \Wirecard\Oxid\Extend\Order $oOrder
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
    private function _makeTransaction(float $fAmount, Order $oOrder): Response
    {
        $sPaymentMethod = $oOrder->oxorder__oxpaymenttype->value;
        $sShopUrl = $this->getConfig()->getCurrentShopUrl();
        $oSession = $this->getSession();

        $oRedirect = self::getRedirectUrls($oSession, $sShopUrl);
        $oPaymentMethod = Payment_Method_Factory::create($sPaymentMethod);
        $oTransactionService = new TransactionService($oPaymentMethod->getConfig(), $this->_oLogger);

        $oTransaction = $oPaymentMethod->getTransaction();
        $oTransaction->setRedirect($oRedirect);

        $oShopconfig = $this->getConfig();
        $oCurrency = $oShopconfig->getActShopCurrencyObject();
        $oTransaction->setAmount(new Amount($fAmount, $oCurrency->name));

        $oBasket = $oSession->getBasket();
        $oUser = $oBasket->getBasketUser();

        $oTransaction->setOrderDetail(sprintf(
            '%s %s %s',
            $oOrder->oxorder__oxbillemail->value,
            $oUser->oxuser__oxfname->value,
            $oUser->oxuser__oxlname->value
        ));

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

        $oTransaction->setNotificationUrl($sShopUrl
            . 'index.php?cl=wcpg_notifyhandler&fnc=handleRequest&pmt='
            . Payment_Method::getOxidFromSDKName($oPaymentMethod->getTransaction()->getConfigKey()));
        $oResponse = $oTransactionService->pay($oTransaction);

        $oOrder->oxorder__wdoxidee_orderstate = new Field(Order::STATE_PENDING);
        $oOrder->save();

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
        $oAccountHolder = new AccountHolder();

        $oAddress = new Address(
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
        $oAddress = new Address(
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

        $oAccount = new AccountHolder();
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
