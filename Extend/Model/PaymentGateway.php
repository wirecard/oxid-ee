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
use Wirecard\Oxid\Model\PaymentMethod\PaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\PaypalPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\RatepayInvoicePaymentMethod;

use Wirecard\PaymentSdk\BackendService;
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
use Wirecard\PaymentSdk\Transaction\SepaDirectDebitTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;
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
     * Paypal, Credit Card and Sofort. have descriptor with maximum of 27 characters.
     * SEPA Direct Debit has descriptor with maximum of 100 characters.
     *
     * @param Transaction $oTransaction the transaction to fill
     * @param string      $sOrderId     the order ID to get the descriptor from
     *
     * @since 1.0.0
     */
    protected static function _addDescriptor(&$oTransaction, $sOrderId)
    {
        $sShopId = Registry::getConfig()->getShopId();
        $oShop = oxNew(Shop::class);
        $oShop->load($sShopId);

        $iDescriptorLength = 27;
        if ($oTransaction instanceof SepaDirectDebitTransaction) {
            $iDescriptorLength = 100;
        }
        $oTransaction->setDescriptor(
            substr(substr($oShop->oxshops__oxname->value, 0, 9) . " " . $sOrderId, 0, $iDescriptorLength)
        );
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
        $sModuleToken = self::setAndRetrieveModuleToken($oSession);
        $sBaseLanguage = Registry::getLang()->getBaseLanguage();

        $oRedirect = new Redirect(
            $sShopUrl . 'index.php?lang=' . $sBaseLanguage . '&cl=order&' . $sModuleToken . $sSid,
            $sShopUrl . 'index.php?lang=' . $sBaseLanguage . '&cl=payment&payerror=-100' . $sSid,
            $sShopUrl . 'index.php?lang=' . $sBaseLanguage . '&cl=payment&payerror=-101' . $sSid
        );

        $oTransaction->setRedirect($oRedirect);
    }

    /**
     * Creates a transaction
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
        $oTransaction->setOrderNumber($oOrder->oxorder__oxid->value);

        if ($oPaymentMethod->getPayment()->oxpayments__wdoxidee_additional_info->value) {
            self::_addAdditionalInfo($oTransaction, $oOrder, $oPaymentMethod->getPayment(), $oSession->getId());
        }

        if ($oPaymentMethod->getPayment()->oxpayments__wdoxidee_descriptor->value) {
            static::_addDescriptor($oTransaction, $oOrder->oxorder__oxid->value);
        }

        if ($this->_shouldAddBasketInfo($oPaymentMethod)) {
            self::_addBasketInfo($oTransaction, $oBasket);
        }

        $this->_addMandatoryTransactionData($oTransaction, $oSession, $oBasket, $oPaymentMethod, $oOrder);
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

        $oPaymentMethod->addMandatoryTransactionData($oTransaction, $oOrder);

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

        $oCustomFields = $oTransaction->getCustomFields();
        if (is_null($oCustomFields)) {
            $oCustomFields = new CustomFieldCollection();
        }

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
        $oPaymentMethod = PaymentMethodFactory::create($oBasket->getPaymentId());

        $oTransactionConfig = $oPaymentMethod->getConfig();
        $oTransactionService = new TransactionService($oTransactionConfig, $this->_oLogger);

        if (!empty(Registry::getRequest()->getRequestParameter('jsresponse'))) {
            return $this->_handleJsResponse(
                $oTransactionService,
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
     * @param \Wirecard\PaymentSdk\Config\Config $oTransactionConfig
     * @param Order                              $oOrder
     *
     * @return Response
     * @throws Exception
     *
     * @since 1.0.0
     */
    private function _handleJsResponse($oTransactionService, $oTransactionConfig, $oOrder)
    {
        $oResponse = $oTransactionService->processJsResponse($_POST, self::getTermUrl());

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
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
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
        $oTransaction->setAccountHolder($oOrder->getAccountHolder());
        $oTransaction->setShipping($oOrder->getShippingAccountHolder());

        $oDevice = new Device($_SERVER['HTTP_USER_AGENT']);
        $sMaid = $oPayment->oxpayments__wdoxidee_maid->value;
        $sDeviceId = Helper::createDeviceFingerprint($sMaid, $sSessionId);

        if ($oPayment->oxpayments__oxid->value === RatepayInvoicePaymentMethod::getName()) {
            $sDeviceId = Registry::getSession()->getVariable(RatepayInvoicePaymentMethod::UNIQUE_TOKEN_VARIABLE);
        }

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
     * @return string
     *
     * @since 1.3.0
     */
    public static function setAndRetrieveModuleToken($oSession)
    {
        $sToken = $oSession->getVariable('wdtoken');

        if (empty($sToken)) {
            $sToken = substr(str_shuffle(md5(time())), 0, 15);
        }

        $oSession->setVariable('wdtoken', $sToken);
        return http_build_query(['wdpayment' => $sToken]);
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

    /**
     * Get the TermUrl for Credit Card Transactions
     *
     * @return string
     *
     * @since 1.3.0
     */
    public static function getTermUrl()
    {
        $sShopUrl = Registry::getConfig()->getShopHomeUrl();
        $sBaseLanguage = Registry::getLang()->getBaseLanguage();
        $sModuleToken = self::setAndRetrieveModuleToken(Registry::getSession());
        return $sShopUrl . 'index.php?lang=' . $sBaseLanguage . '&cl=order&redirectFromForm=1&'
            . $sModuleToken . Helper::getSidQueryString();
    }
}
