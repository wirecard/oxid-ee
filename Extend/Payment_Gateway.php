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
    const NAME = 'wdpaypal';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $oLogger;

    /**
     * BasePaymentGateway constructor.
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function __construct()
    {
        $this->oLogger = Registry::getLogger();
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
        if (!$oOrder->isWirecardPaymentType()) {
            return parent::executePayment($dAmount, $oOrder);
        }
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

        $sErrorText = Helper::translate('order_error');
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

        $oRedirect = self::getRedirectUrls($oSession, $sShopUrl);
        $oPaymentMethod = Payment_Method_Factory::create(self::NAME);
        $oTransactionService = new TransactionService($oPaymentMethod->getConfig(), $this->oLogger);

        $oTransaction = $oPaymentMethod->getTransaction();
        $oTransaction->setRedirect($oRedirect);

        $oShopconfig = $this->getConfig();
        $oCurrency = $oShopconfig->getActShopCurrencyObject();
        $oTransaction->setAmount(new Amount($dAmount, $oCurrency->name));

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

        $oTransaction->setNotificationUrl($sShopUrl . 'notify.php');
        $oResponse = $oTransactionService->pay($oTransaction);
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
            $this->_addArticleToBasket($oWdBasket, $key, $value, $finalPrices, $oCurrency);
        }

        // include shipping costs in basket
        $this->_addShippingCostsToBasket($oWdBasket, $oBasket, $oCurrency);

        // include voucher discounts in basket
        $this->_addVoucherDiscountsToBasket($oWdBasket, $oBasket, $oCurrency);

        // include wrapping costs in basket
        $this->_addWrappingCostsToBasket($oWdBasket, $oBasket, $oCurrency);

        // include gift card costs in basket
        $this->_addGiftCardCostsToBasket($oWdBasket, $oBasket, $oCurrency);

        // include payment costs in basket
        $this->_addPaymentCostsToBasket($oWdBasket, $oBasket, $oCurrency);

        $oTransaction->setBasket($oWdBasket);
    }

    /**
     * Adds an article to the basket
     *
     * @param WdBasket $oBasket
     * @param string   $sArticleKey
     * @param int      $iQuantity
     * @param array    $aPrices
     * @param object   $oCurrency
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    private function _addArticleToBasket(
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

    /**
     * Adds the shipping costs to the basket
     *
     * @param WdBasket $oWdBasket the paymentSDK basket item
     * @param Basket   $oBasket   the OXID basket item
     * @param object   $oCurrency
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    private function _addShippingCostsToBasket(WdBasket &$oWdBasket, Basket $oBasket, $oCurrency)
    {
        $oShippingCost = $oBasket->getDeliveryCost();

        if ($oShippingCost && !empty($oShippingCost->getPrice())) {
            $item = new Item(
                Helper::translate('shipping_title'),
                new Amount($oShippingCost->getBruttoPrice(), $oCurrency->name),
                1
            );
            $item->setTaxRate($oShippingCost->getVat());
            $item->setTaxAmount(new Amount($oShippingCost->getVatValue(), $oCurrency->name));

            $oWdBasket->add($item);
        }
    }

    /**
     * Adds all voucher discounts to the basket
     *
     * @param WdBasket $oWdBasket the paymentSDK basket item
     * @param Basket   $oBasket   the OXID basket item
     * @param object   $oCurrency
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    private function _addVoucherDiscountsToBasket(WdBasket &$oWdBasket, Basket $oBasket, $oCurrency)
    {
        $aVouchers = $oBasket->getVouchers();

        if (count($aVouchers) > 0) {
            foreach ($aVouchers as $oVoucher) {
                $oItem = new Item(
                    Helper::translate('voucher'),
                    new Amount(round($oVoucher->dVoucherdiscount * -1, 2), $oCurrency->name)
                );

                $oWdBasket->add($oItem);
            }
        }
    }

    /**
     * Adds all wrapping costs to the basket
     *
     * @param WdBasket $oWdBasket the paymentSDK basket item
     * @param Basket   $oBasket   the OXID basket item
     * @param object   $oCurrency
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    private function _addWrappingCostsToBasket(WdBasket &$oWdBasket, Basket $oBasket, $oCurrency)
    {
        $oWrappingCost = $oBasket->getWrappingCost();

        if ($oWrappingCost && !empty($oWrappingCost->getPrice())) {
            $item = new Item(
                Helper::translate('wrapping'),
                new Amount($oWrappingCost->getBruttoPrice(), $oCurrency->name),
                1
            );
            $item->setTaxRate($oWrappingCost->getVat());
            $item->setTaxAmount(new Amount($oWrappingCost->getVatValue(), $oCurrency->name));

            $oWdBasket->add($item);
        }
    }

    /**
     * Adds all gift card costs to the basket
     *
     * @param WdBasket $oWdBasket the paymentSDK basket item
     * @param Basket   $oBasket   the OXID basket item
     * @param object   $oCurrency
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    private function _addGiftCardCostsToBasket(WdBasket &$oWdBasket, Basket $oBasket, $oCurrency)
    {
        $oGiftCardCost = $oBasket->getGiftCardCost();

        if ($oGiftCardCost && !empty($oGiftCardCost->getPrice())) {
            $item = new Item(
                Helper::translate('gift_card'),
                new Amount($oGiftCardCost->getBruttoPrice(), $oCurrency->name),
                1
            );
            $item->setTaxRate($oGiftCardCost->getVat());
            $item->setTaxAmount(new Amount($oGiftCardCost->getVatValue(), $oCurrency->name));

            $oWdBasket->add($item);
        }
    }

    /**
     * Adds all payment costs to the basket
     *
     * @param WdBasket $oWdBasket the paymentSDK basket item
     * @param Basket   $oBasket   the OXID basket item
     * @param object   $oCurrency
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    private function _addPaymentCostsToBasket(WdBasket &$oWdBasket, Basket $oBasket, $oCurrency)
    {
        $oPaymentCost = $oBasket->getPaymentCost();

        if ($oPaymentCost && !empty($oPaymentCost->getPrice())) {
            $item = new Item(
                Helper::translate('payment_cost'),
                new Amount($oPaymentCost->getBruttoPrice(), $oCurrency->name),
                1
            );
            $item->setTaxRate($oPaymentCost->getVat());
            $item->setTaxAmount(new Amount($oPaymentCost->getVatValue(), $oCurrency->name));

            $oWdBasket->add($item);
        }
    }
}
