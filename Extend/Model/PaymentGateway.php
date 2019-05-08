<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend\Model;

use Exception;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Application\Model\Shop;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;

use Psr\Log\LoggerInterface;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\OrderHelper;
use Wirecard\Oxid\Core\PaymentMethodFactory;
use Wirecard\Oxid\Model\PaymentMethod;
use Wirecard\Oxid\Model\PaypalPaymentMethod;

use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\Device;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;
use Wirecard\PaymentSdk\Transaction\SepaDirectDebitTransaction;
use Wirecard\PaymentSdk\TransactionService;

/**
 * Custom Payment Gateway to handle Module payment methods
 *
 * @since 1.0.0
 */
class PaymentGateway extends BaseModel
{

    /**
     * @var LoggerInterface
     *
     * @since 1.0.0
     */
    private $_oLogger;

    /**
     * PaymentGateway constructor.
     *
     * @since 1.0.0
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
     * @since 1.0.0
     */
    private static function _addDescriptor(&$oTransaction, $sOrderId)
    {
        $sShopId = Registry::getConfig()->getShopId();
        $oShop = oxNew(Shop::class);
        $oShop->load($sShopId);
        $oTransaction->setDescriptor(substr(substr($oShop->oxshops__oxname->value, 0, 9) . " " . $sOrderId, 0, 27));
    }

    /**
     * Returns a redirect object
     *
     * @param Transaction $oTransaction the transaction
     * @param Session     $oSession     the current session object
     * @param string      $sShopUrl     the public url for the shop
     * @param string      $sSid         the query formated session id eg. "&sid=123456789"
     *
     * @since 1.0.0
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
     * @since 1.0.0
     */
    public function createTransaction($oBasket, $oOrder)
    {
        $oSession = $this->getSession();

        $sPaymentId = $oBasket->getPaymentId();
        $oPaymentMethod = PaymentMethodFactory::create($sPaymentId);
        $oTransaction = $oPaymentMethod->getTransaction();

        $this->_addMandatoryTransactionData($oTransaction, $oSession, $oBasket, $oPaymentMethod, $oOrder);

        if ($oPaymentMethod->getPayment()->oxpayments__wdoxidee_additional_info->value) {
            self::_addAdditionalInfo($oTransaction, $oOrder, $oPaymentMethod->getPayment(), $oSession->getId());
        }

        if ($oPaymentMethod->getPayment()->oxpayments__wdoxidee_descriptor->value) {
            self::_addDescriptor($oTransaction, $oOrder->oxorder__oxid->value);
        }

        if ($this->_shouldAddBasketInfo($oPaymentMethod)) {
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
     *
     * @since 1.0.0
     */
    private function _addMandatoryTransactionData(&$oTransaction, $oSession, $oBasket, $oPaymentMethod, $oOrder)
    {
        $oShopconfig = Registry::getConfig();
        $sShopUrl = $oShopconfig->getCurrentShopUrl();
        $sBaseLanguage = Registry::getLang()->getBaseLanguage();

        $sSid = Helper::getSidQueryString();

        self::_addRedirectUrls($oTransaction, $oSession, $sShopUrl, $sSid);

        $oCurrency = $oShopconfig->getActShopCurrencyObject();
        $oTransaction->setAmount(new Amount($oBasket->getPrice()->getBruttoPrice(), $oCurrency->name));

        $oTransaction->setNotificationUrl($sShopUrl
            . 'index.php?cl=wcpg_notifyhandler&fnc=handleRequest&pmt='
            . PaymentMethod::getOxidFromSDKName($oPaymentMethod->getTransaction()->getConfigKey())
            . '&lang=' . $sBaseLanguage);

        if ($oTransaction instanceof PayPalTransaction) {
            $sOrderDetails = $oOrder->oxorder__oxremark->value;

            if (!empty($sOrderDetails)) {
                $oTransaction->setOrderDetail($sOrderDetails);
            }
        }

        if ($oTransaction instanceof SepaDirectDebitTransaction) {
            $oAccountHolder = new AccountHolder();
            $oAccountHolder->setLastName(PaymentMethodHelper::getAccountHolder());
            $oTransaction->setAccountHolder($oAccountHolder);
        }

        $this->_addCustomFields($oTransaction);
    }

    /**
     * @param Transaction $oTransaction
     *
     * @since 1.0.0
     */
    private function _addCustomFields(&$oTransaction)
    {
        $aShopInfoFields = Helper::getShopInfoFields();

        $oCustomFields = new CustomFieldCollection();
        $oCustomFields->add(new CustomField(Helper::SHOP_NAME_KEY, $aShopInfoFields[HELPER::SHOP_NAME_KEY]));
        $oCustomFields->add(new CustomField(Helper::SHOP_VERSION_KEY, $aShopInfoFields[Helper::SHOP_VERSION_KEY]));

        $oCustomFields->add(new CustomField(Helper::PLUGIN_NAME_KEY, $aShopInfoFields[Helper::PLUGIN_NAME_KEY]));
        $oCustomFields->add(new CustomField(Helper::PLUGIN_VERSION_KEY, $aShopInfoFields[Helper::PLUGIN_VERSION_KEY]));

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
     *
     * @since 1.0.0
     */
    public function executeTransaction($oTransaction, $oOrder, $oBasket)
    {
        $oSession = $this->getSession();

        $oPaymentMethod = PaymentMethodFactory::create($oBasket->getPaymentId());

        $oTransactionConfig = $oPaymentMethod->getConfig();
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
            $oPaymentMethod->getPayment()->oxpayments__wdoxidee_transactionaction->value
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
     *
     * @since 1.0.0
     */
    private function _handleJsResponse($oTransactionService, $oSession, $sShopUrl, $sSid, $oTransactionConfig, $oOrder)
    {
        $sModuleToken = self::getModuleToken($oSession);
        $sBaseLanguage = Registry::getLang()->getBaseLanguage();
        $oResponse = $oTransactionService->processJsResponse(
            $_POST,
            $sShopUrl . 'index.php?lang=' . $sBaseLanguage . '&cl=order&redirectFromForm=1&' . $sModuleToken . $sSid
        );

        $oBackendService = new BackendService($oTransactionConfig, $this->_oLogger);
        OrderHelper::handleResponse($oResponse, $this->_oLogger, $oOrder, $oBackendService);

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
     * @since 1.0.0
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
        if (!$oTransaction instanceof SepaDirectDebitTransaction) {
            $oTransaction->setAccountHolder($oOrder->getAccountHolder());
        }
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
     * @since 1.0.0
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
     *
     * @since 1.0.0
     */
    public static function getModuleToken($oSession)
    {
        $sRandom = substr(str_shuffle(md5(time())), 0, 15);
        $oSession->setVariable('wdtoken', $sRandom);
        return http_build_query(['wdpayment' => $sRandom]);
    }

    /**
     * Checks if basket info should be added
     *
     * @param PaymentMethod $oPaymentMethod
     *
     * @return bool
     *
     * @since 1.0.0
     */
    private function _shouldAddBasketInfo($oPaymentMethod)
    {
        if ($oPaymentMethod instanceof PaypalPaymentMethod) {
            return !!$oPaymentMethod->getPayment()->oxpayments__wdoxidee_basket->value;
        }

        return !!$oPaymentMethod->getPayment()->oxpayments__wdoxidee_additional_info->value;
    }
}
