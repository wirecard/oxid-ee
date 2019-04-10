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
use \OxidEsales\Eshop\Application\Model\Basket;
use \OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Module\Module;
use \OxidEsales\Eshop\Core\Registry;
use \OxidEsales\Eshop\Core\Session;

use OxidEsales\EshopCommunity\Core\Config;
use OxidEsales\EshopCommunity\Core\Model\BaseModel;
use OxidEsales\EshopCommunity\Core\ShopVersion;
use \Wirecard\Oxid\Core\Helper;
use \Wirecard\Oxid\Core\PaymentMethodFactory;

use Wirecard\Oxid\Core\ResponseHandler;
use Wirecard\Oxid\Model\PaymentMethod;
use Wirecard\PaymentSdk\BackendService;
use \Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use \Wirecard\PaymentSdk\Entity\Device;
use \Wirecard\PaymentSdk\Response\FormInteractionResponse;
use \Wirecard\PaymentSdk\Response\Response;
use \Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use \Wirecard\PaymentSdk\Transaction\Transaction;
use \Wirecard\PaymentSdk\TransactionService;
use \Wirecard\PaymentSdk\Response\FailureResponse;
use \Wirecard\PaymentSdk\Response\InteractionResponse;
use \Wirecard\PaymentSdk\Entity\Redirect;

use \Psr\Log\LoggerInterface;
use \Exception;

/**
 * Custom Payment Gateway to handle Module payment methods
 */
class PaymentGateway extends BaseModel
{
    /**
     * @var LoggerInterface
     */
    private $_oLogger;

    /**
     * PaymentGateway constructor.
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->_oLogger = Registry::getLogger();
    }

    /**
     * Returns a descriptor
     *
     * If you want to customize the descriptor, override this function.
     *
     * @param Transaction $oTransaction the transaction to fill
     * @param string      $sOrderId     the order ID to get the descriptor from
     *
     */
    private static function _addDescriptor(&$oTransaction, $sOrderId)
    {
        $shopId = Registry::getConfig()->getShopId();
        $shop = oxNew(Shop::class);
        $shop->load($shopId);
        $oTransaction->setDescriptor(substr($shop->oxshops__oxname->value, 0, 9) . " " . $sOrderId);
    }

    /**
     * Returns a redirect object
     *
     * @param Transaction $oTransaction the transaction
     * @param Session     $oSession     the current session object
     * @param string      $sShopUrl     the public url for the shop
     * @param string      $sSid         the query formated session id eg. "&sid=123456789"
     *
     */
    private static function _addRedirectUrls(&$oTransaction, $oSession, $sShopUrl, $sSid)
    {
        $sModuleToken = self::getModuleToken($oSession);
        $sBaseLanguage = Registry::getLang()->getBaseLanguage();

        $oRedirect = new Redirect(
            $sShopUrl . 'index.php?lang=' . $sBaseLanguage . '&cl=order&' . $sModuleToken . $sSid,
            $sShopUrl . 'index.php?lang=' . $sBaseLanguage . '&cl=payment&payerror=-100' . $sSid,
            $sShopUrl . 'index.php?lang=' . $sBaseLanguage . '&cl=payment&payerror=-101' . $sSid
        );

        $oTransaction->setRedirect($oRedirect);
    }

    /**
     * Executes the transaction through EE
     *
     * @param Basket $oBasket
     * @param Order  $oOrder
     *
     * @return Transaction
     *
     * @throws Exception
     *
     */
    public function createTransaction($oBasket, $oOrder)
    {
        $oSession = $this->getSession();

        /**
         * @var $oPayment Payment
         */
        $oPayment = oxNew(Payment::class);
        $oPayment->load($oBasket->getPaymentId());

        $oPaymentMethod = PaymentMethodFactory::create($oPayment->oxpayments__oxid->value);

        $oTransaction = $oPaymentMethod->getTransaction();

        $this->_addMandatoryTransactionData($oTransaction, $oSession, $oBasket, $oPaymentMethod, $oOrder);

        if ($oPayment->oxpayments__wdoxidee_additional_info->value) {
            self::_addAdditionalInfo($oTransaction, $oOrder, $oPayment, $oSession->getId());
        }

        if ($oPayment->oxpayments__wdoxidee_descriptor->value) {
            self::_addDescriptor($oTransaction, $oOrder->oxorder__oxid->value);
        }

        if ($oPayment->oxpayments__wdoxidee_basket->value) {
            self::_addBasketInfo($oTransaction, $oBasket);
        }

        return $oTransaction;
    }

    /**
     * @param Transaction   $oTransaction
     * @param Session       $oSession
     * @param Basket        $oBasket
     * @param PaymentMethod $oPaymentMethod
     * @param Order         $oOrder
     */
    private function _addMandatoryTransactionData(&$oTransaction, $oSession, $oBasket, $oPaymentMethod, $oOrder)
    {
        $oShopconfig = Registry::getConfig();
        $sShopUrl = $oShopconfig->getCurrentShopUrl();

        $sSid = Helper::getSidQueryString();

        self::_addRedirectUrls($oTransaction, $oSession, $sShopUrl, $sSid);

        $oCurrency = $oShopconfig->getActShopCurrencyObject();
        $oTransaction->setAmount(new Amount($oBasket->getPrice()->getBruttoPrice(), $oCurrency->name));

        $oTransaction->setNotificationUrl($sShopUrl
            . 'index.php?cl=wcpg_notifyhandler&fnc=handleRequest&pmt='
            . PaymentMethod::getOxidFromSDKName($oPaymentMethod->getTransaction()->getConfigKey()));

        if ($oTransaction instanceof PayPalTransaction) {
            $sOrderDetails = $oOrder->oxorder__oxremark->value;

            if (!empty($sOrderDetails)) {
                $oTransaction->setOrderDetail($sOrderDetails);
            }
        }

        $this->_addCustomFields($oTransaction);
    }

    /**
     * @param Transaction $oTransaction
     */
    private function _addCustomFields(&$oTransaction)
    {
        $shopId = Registry::getConfig()->getShopId();
        $shop = oxNew(Shop::class);
        $shop->load($shopId);

        $oCustomFields = new CustomFieldCollection();
        $oCustomFields->add(new CustomField('shopName', $shop->oxshops__oxname->value));
        $oCustomFields->add(new CustomField('shopVersion', ShopVersion::getVersion()));

        $oModule = oxNew(Module::class);
        $oModule->load('wdoxidee');

        $oCustomFields->add(new CustomField('pluginName', $oModule->getTitle()));
        $oCustomFields->add(new CustomField('pluginVersion', $oModule->getInfo('version')));

        $oTransaction->setCustomFields($oCustomFields);
    }

    /**
     * Calls the {@link TransactionService} and processes the {@link Transaction}
     *
     * @param Transaction $oTransaction
     * @param Order       $oOrder
     *
     * @param Basket      $oBasket
     *
     * @return FailureResponse|FormInteractionResponse|InteractionResponse|Response|SuccessResponse
     * @throws Exception
     */
    public function executeTransaction($oTransaction, $oOrder, $oBasket)
    {
        $oSession = $this->getSession();
        /**
         * @var $oPayment Payment
         */
        $oPayment = oxNew(Payment::class);
        $oPayment->load($oBasket->getPaymentId());

        $oPaymentMethod = PaymentMethodFactory::create($oPayment->oxpayments__oxid->value);

        $oTransactionConfig = $oPaymentMethod->getConfig($oPayment);
        $oTransactionService = new TransactionService($oTransactionConfig, $this->_oLogger);

        $oShopConfig = Registry::getConfig();

        if (!empty($oShopConfig->getRequestParameter('jsresponse'))) {
            return $this->_handleJsResponse(
                $oTransactionService,
                $oSession,
                $oShopConfig->getCurrentShopUrl(),
                Helper::getSidQueryString(),
                $oTransactionConfig,
                $oOrder
            );
        }

        $oResponse = $oTransactionService->process(
            $oTransaction,
            $oPayment->oxpayments__wdoxidee_transactionaction->value
        );

        return $oResponse;
    }

    /**
     * @param TransactionService                 $oTransactionService
     * @param Session                            $oSession
     * @param string                             $sShopUrl
     * @param string                             $sSid
     * @param \Wirecard\PaymentSdk\Config\Config $oTransactionConfig
     * @param Order                              $oOrder
     *
     * @return Response
     * @throws Exception
     */
    private function _handleJsResponse($oTransactionService, $oSession, $sShopUrl, $sSid, $oTransactionConfig, $oOrder)
    {
        $sModuleToken = self::getModuleToken($oSession);
        $sBaseLanguage = Registry::getLang()->getBaseLanguage();
        $oResponse = $oTransactionService->processJsResponse(
            $_POST,
            $sShopUrl . 'index.php?lang=' . $sBaseLanguage . '&cl=order&redirectFromForm=1&' . $sModuleToken . $sSid
        );
        if ($oResponse instanceof SuccessResponse) {
            $oBackendService = new BackendService($oTransactionConfig, $this->_oLogger);
            ResponseHandler::onSuccessResponse($oResponse, $oBackendService, $oOrder);
        }
        return $oResponse;
    }

    /**
     * Add additional info to the transaction
     *
     * @param Transaction $oTransaction
     * @param Order       $oOrder
     * @param Payment     $oPayment
     * @param string      $sSessionId
     *
     */
    private static function _addAdditionalInfo(
        &$oTransaction,
        $oOrder,
        $oPayment,
        $sSessionId
    ) {
        $sRemoteAddress = Registry::getUtilsServer()->getRemoteAddress();

        $oTransaction->setIpAddress($sRemoteAddress);
        $oTransaction->setOrderNumber($oOrder->oxorder__oxid->value);
        $oTransaction->setAccountHolder($oOrder->getAccountHolder());
        $oTransaction->setShipping($oOrder->getShippingAccountHolder());

        $oDevice = new Device($_SERVER['HTTP_USER_AGENT']);
        $sMaid = $oPayment->oxpayments__wdoxidee_maid->value;
        $sDeviceId = Helper::createDeviceFingerprint($sMaid, $sSessionId);
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
     */
    private static function _addBasketInfo(&$oTransaction, $oBasket)
    {
        $oWdBasket = $oBasket->createTransactionBasket();
        $oWdBasket->setVersion($oTransaction);
        $oTransaction->setBasket($oWdBasket);
    }

    /**
     * @param Session $oSession
     *
     * @return bool|string
     */
    public static function getModuleToken($oSession)
    {
        $sRandom = substr(str_shuffle(md5(time())), 0, 15);
        $oSession->setVariable('wdtoken', $sRandom);
        return http_build_query(array('wdpayment' => $sRandom));
    }
}
