<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend\Model;

use \OxidEsales\Eshop\Application\Model\Shop;
use \OxidEsales\Eshop\Application\Model\Article;
use \OxidEsales\Eshop\Application\Model\Country;
use \OxidEsales\Eshop\Application\Model\Basket;
use \OxidEsales\Eshop\Application\Model\BasketItem;
use \OxidEsales\Eshop\Application\Model\State;
use \OxidEsales\Eshop\Application\Model\Payment;
use \OxidEsales\Eshop\Application\Model\User;
use \OxidEsales\Eshop\Core\Field;
use \OxidEsales\Eshop\Core\Registry;
use \OxidEsales\Eshop\Core\Session;

use \Wirecard\Oxid\Core\BasketHelper;
use \Wirecard\Oxid\Core\Helper;
use \Wirecard\Oxid\Core\Payment_Method_Factory;
use \Wirecard\Oxid\Model\Payment_Method;

use \Wirecard\PaymentSdk\Entity\Amount;
use \Wirecard\PaymentSdk\Entity\Device;
use \Wirecard\PaymentSdk\Response\Response;
use \Wirecard\PaymentSdk\Response\SuccessResponse;
use \Wirecard\PaymentSdk\Transaction\Transaction;
use \Wirecard\PaymentSdk\TransactionService;
use \Wirecard\PaymentSdk\Response\FailureResponse;
use \Wirecard\PaymentSdk\Response\InteractionResponse;
use \Wirecard\PaymentSdk\Entity\Redirect;
use \Wirecard\PaymentSdk\Entity\Address;

use \Wirecard\Oxid\Extend\Model\Order;
use \Psr\Log\LoggerInterface;
use \Exception;

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
     * BasePaymentGateway constructor.
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function __construct()
    {
        $this->_oLogger = Registry::getLogger();
    }

    /**
     * Returns a descriptor
     *
     * If you want to customize the descriptor, override this function.
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
        return substr($shop->oxshops__oxname->value, 0, 9) . " " . $sOrderId;
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

        $sRandom = substr(str_shuffle(md5(time())), 0, 15);
        $oSession->setVariable("wdtoken", $sRandom);

        $sBaseLanguage = Registry::getLang()->getBaseLanguage();

        $oRedirect = new Redirect(
            $sShopUrl . 'index.php?lang=' . $sBaseLanguage . '&cl=order&wdpayment=' . $sRandom . $sSid,
            $sShopUrl . 'index.php?lang=' . $sBaseLanguage . '&cl=payment&payerror=-100' . $sSid,
            $sShopUrl . 'index.php?lang=' . $sBaseLanguage . '&cl=payment&payerror=-101' . $sSid
        );

        return $oRedirect;
    }

    /**
     * Executes the transaction through EE
     *
     * @param float $fAmount
     * @param Order $oOrder
     *
     * @return FailureResponse|InteractionResponse|Response|SuccessResponse
     *
     * @throws Exception
     *
     * @SuppressWarnings(PHPMD.Coverage)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function makeTransaction(float $fAmount, Order $oOrder): Response
    {
        $sShopUrl = $this->getConfig()->getCurrentShopUrl();
        $oSession = $this->getSession();
        $oBasket = $oSession->getBasket();
        $sPaymentMethod = $oBasket->getPaymentId();

        $oRedirect = self::getRedirectUrls($oSession, $sShopUrl);
        $oPaymentMethod = Payment_Method_Factory::create($sPaymentMethod);
        $oTransactionService = new TransactionService($oPaymentMethod->getConfig(), $this->_oLogger);

        $oTransaction = $oPaymentMethod->getTransaction();
        $oTransaction->setRedirect($oRedirect);

        $oShopconfig = $this->getConfig();
        $oCurrency = $oShopconfig->getActShopCurrencyObject();
        $oTransaction->setAmount(new Amount($fAmount, $oCurrency->name));

        $oBasket = $oSession->getBasket();

        $sOrderDetails = $oOrder->oxorder__oxremark->value;
        if (!empty($sOrderDetails)) {
            $oTransaction->setOrderDetail($sOrderDetails);
        }

        $oPayment = oxNew(Payment::class);
        $oPayment->load($sPaymentMethod);

        if ($oPayment->oxpayments__wdoxidee_additional_info->value) {
            $this->_addAdditionalInfo($oTransaction, $oOrder, $oPayment);
        }

        if ($oPayment->oxpayments__wdoxidee_descriptor->value) {
            $sDescriptor = self::getDescriptor($oOrder->oxorder__oxid->value);
            $oTransaction->setDescriptor($sDescriptor);
        }

        if ($oPayment->oxpayments__wdoxidee_basket->value) {
            $this->_addBasketInfo($oTransaction, $oBasket);
        }

        $oTransaction->setNotificationUrl($sShopUrl
            . 'index.php?cl=wcpg_notifyhandler&fnc=handleRequest&pmt='
            . Payment_Method::getOxidFromSdkName($oPaymentMethod->getTransaction()->getConfigKey()));

        $oResponse = $oTransactionService->process(
            $oTransaction,
            $oPayment->oxpayments__wdoxidee_transactionaction->value
        );

        return $oResponse;
    }

    /**
     * Add additional info to the transaction
     *
     * @param Transaction $oTransaction
     * @param Order       $oOrder
     * @param Payment     $oPayment
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    private function _addAdditionalInfo(
        Transaction &$oTransaction,
        Order $oOrder,
        Payment $oPayment
    ) {
        $sRemoteAddress = Registry::getUtilsServer()->getRemoteAddress();

        $oTransaction->setIpAddress($sRemoteAddress);
        $oTransaction->setOrderNumber($oOrder->oxorder__oxid->value);
        $oTransaction->setAccountHolder($oOrder->getAccountHolder());
        $oTransaction->setShipping($oOrder->getShippingAccountHolder());

        $oDevice = new Device($_SERVER['HTTP_USER_AGENT']);
        $sMaid = $oPayment->oxpayments__wdoxidee_maid->value;
        $sDeviceId = Helper::createDeviceFingerprint($sMaid, $this->getSession()->getId());
        $oDevice->setFingerprint($sDeviceId);
        $oTransaction->setDevice($oDevice);
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
        $oWdBasket = $oBasket->createTransactionBasket();
        $oWdBasket->setVersion($oTransaction);
        $oTransaction->setBasket($oWdBasket);
    }
}
