<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend\Controller;

use Exception;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\OrderHelper;
use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\Oxid\Extend\Model\Order;
use Wirecard\Oxid\Extend\Model\Payment;
use Wirecard\Oxid\Extend\Model\PaymentGateway;

use Wirecard\Oxid\Model\PaymentMethod\CreditCardPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\PayolutionBtwobPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\PayolutionInvoicePaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\SepaDirectDebitPaymentMethod;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\TransactionService;

/**
 * Class Order
 *
 * @mixin \OxidEsales\Eshop\Application\Controller\OrderController
 *
 * @since 1.0.0
 */
class OrderController extends OrderController_parent
{
    const FORM_POST_VARIABLE = 'formPost';
    const SEPA_MANDATE_KEY = 'sepamandate';

    /**
     * @var TransactionService
     *
     * @since 1.0.0
     */
    private $_oTransactionService;

    /**
     * @var CreditCardPaymentMethod
     *
     * @since 1.0.0
     */
    private $_oCcPaymentMethod;

    /**
     * @var Config
     *
     * @since 1.0.0
     */
    private $_oPaymentMethodConf;

    /**
     * Extends the parent init function and finalizes the order in case it was a Wirecard payment method
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function init()
    {
        parent::init();

        OrderHelper::onBeforeOrderCreation($this->getPayment());

        $oSession = Registry::getSession();
        $sWdPaymentRedirect = Registry::getRequest()->getRequestParameter('wdpayment');
        $sWdSessionToken = $oSession->getVariable('wdtoken');

        if (OrderHelper::isPaymentFinished($sWdSessionToken, $sWdPaymentRedirect)) {
            $sShopBaseUrl = Registry::getConfig()->getShopUrl();
            $sLanguageCode = Registry::getLang()->getBaseLanguage();

            $aParams = [
                'lang' => $sLanguageCode,
                'force_sid' => $oSession->getId(),
                'stoken' => $oSession->getSessionChallengeToken(),
                'actcontrol' => 'order',
                'cl' => 'order',
                'fnc' => 'execute',
                'sDeliveryAddressMD5' => $this->getDeliveryAddressMD5(),
                'challenge' => '',
                'ord_agb' => '1',
                'oxdownloadableproductsagreement' => '0',
                'oxserviceproductsagreement' => '0',
                'wdtoken' => $sWdSessionToken,
                'wdfinishedpayment' => true,
            ];

            if (Registry::getRequest()->getRequestParameter('redirectFromForm')) {
                $oSession->setVariable(self::FORM_POST_VARIABLE, $_POST);
            }

            $sParamStr = http_build_query($aParams);

            return Registry::getUtils()->redirect($sShopBaseUrl . 'index.php?' . $sParamStr, false);
        }

        return true;
    }

    /**
     * Extends the parent render function
     *
     * @return string
     *
     * @since 1.1.0
     */
    public function render()
    {
        // after calling parent::render() we are sure we will have order id stored in the session
        // order id is needed in sepa mandate
        $sTemplateName = parent::render();
        $oBasket = Registry::getSession()->getBasket();

        if ($oBasket->getPaymentId() === SepaDirectDebitPaymentMethod::getName()) {
            $sSepaMandate = PaymentMethodHelper::getSepaMandateHtml($oBasket, $this->getUser());

            Helper::addToViewData($this, [
                self::SEPA_MANDATE_KEY => $sSepaMandate,
            ]);
        }

        return $sTemplateName;
    }

    /**
     * Redirects to the PaymentSDK if the payment is a Module's method
     *
     * @inheritdoc
     *
     * @return string
     *
     * @throws \Exception
     *
     * @since 1.0.0
     */
    public function execute()
    {
        $oSession = Registry::getSession();
        $oBasket = $oSession->getBasket();
        $oPayment = PaymentMethodHelper::getPaymentById($oBasket->getPaymentId());

        if (!$oPayment->isCustomPaymentMethod()) {
            return parent::execute();
        }

        $oOrder = oxNew(Order::class);
        $bIsOrderLoaded = OrderHelper::loadOrderWithSessionChallenge($oOrder);
        $sWdPaymentRedirect = Registry::getRequest()->getRequestParameter('wdfinishedpayment');
        // necessary to prevent order being overwritten when consumer does not correctly finalise eps payment
        if ($bIsOrderLoaded && !$sWdPaymentRedirect) {
            Registry::getSession()->setVariable(
                'sess_challenge',
                $this->getUtilsObjectInstance()->generateUID()
            );
            $bIsOrderLoaded = OrderHelper::loadOrderWithSessionChallenge($oOrder);
        }
        return $this->_determineNextStep($oOrder, $bIsOrderLoaded, $oPayment);
    }

    /**
     * Determines the next step to be shown during the checkout process.
     *
     * @param Order   $oOrder
     * @param boolean $bIsOrderLoaded
     * @param Payment $oPayment
     *
     * @return mixed integer/string
     * @throws \Exception
     *
     * @since 1.0.0
     */
    private function _determineNextStep($oOrder, $bIsOrderLoaded, $oPayment)
    {
        $oSession = Registry::getSession();
        $oBasket = $oSession->getBasket();
        $oConfig = Registry::getConfig();
        $iSuccess = 0;

        $sWdPaymentRedirect = $oConfig->getRequestParameter('wdtoken');
        $sWdSessionToken = $oSession->getVariable('wdtoken');

        if ($bIsOrderLoaded) {
            $iSuccess = $oOrder->oxorder__wdoxidee_finalizeorderstate->value;
        }

        if (OrderHelper::isPaymentFinished($sWdSessionToken, $sWdPaymentRedirect)) {
            OrderHelper::handleFormResponse($oSession, $oPayment, $oOrder, self::FORM_POST_VARIABLE);
            return $this->_getNextStep($iSuccess);
        }

        // delete old order if payment was canceled
        if ($bIsOrderLoaded) {
            $sOrderId = Helper::getSessionChallenge();
            $oOrder->delete($sOrderId);
        }

        $oUser = $this->getUser();

        return $this->_processOrderTransaction($oOrder, $oBasket, $oUser);
    }

    /**
     * Checks if the order is valid, contains basket items and then processes the transaction.
     *
     * @param Order  $oOrder
     * @param Basket $oBasket
     * @param User   $oUser
     *
     * @return mixed integer/string
     *
     * @throws Exception
     *
     * @since 1.0.0
     */
    private function _processOrderTransaction($oOrder, $oBasket, $oUser)
    {
        // check if order should be created first
        if (!$this->_shouldCreateOrder($oUser)) {
            return $this->_getNextStep(Order::ORDER_STATE_PAYMENTERROR);
        }

        if ($oBasket->getProductsCount()) {
            $sSepaMandate = $this->_prepareSepaMandate($oBasket, $oUser);
            $oOrder = OrderHelper::createOrder(
                $oBasket,
                $oUser,
                $sSepaMandate
            );

            if (!$oOrder) {
                $iSuccess = $oOrder->oxorder__wdoxidee_finalizeorderstate->value;
                return $this->_getNextStep($iSuccess);
            }

            return $this->_handleTransaction($oBasket, $oOrder);
        }

        $iSuccess = $oOrder->oxorder__wdoxidee_finalizeorderstate->value;
        return $this->_getNextStep($iSuccess);
    }

    /**
     * Prepares SEPA mandate text
     *
     * @param Basket $oBasket
     * @param User   $oUser
     *
     * @return null|string
     *
     * @since 1.1.0
     */
    private function _prepareSepaMandate($oBasket, $oUser)
    {
        $sSepaMandate = null;
        if ($oBasket->getPaymentId() === SepaDirectDebitPaymentMethod::getName()) {
            $sSepaMandate = PaymentMethodHelper::getSepaMandateHtml($oBasket, $oUser);
        }
        return $sSepaMandate;
    }

    /**
     * Makes multiple checks if the order should be created
     *
     * @param User $oUser
     *
     * @return bool
     *
     * @since 1.0.0
     */
    private function _shouldCreateOrder($oUser)
    {
        if (!$this->getSession()->checkSessionChallenge()) {
            return false;
        }

        if (!$this->_validateTermsAndConditions()) {
            $this->_blConfirmAGBError = true;
            return false;
        }

        // additional check if we really really have a user now
        if (!$oUser) {
            return false;
        }

        return true;
    }

    /**
     * Handles payment transaction
     *
     * @param Basket $oBasket
     * @param Order  $oOrder
     *
     * @return string
     * @throws Exception
     *
     * @since 1.0.0
     */
    private function _handleTransaction($oBasket, $oOrder)
    {
        $oLogger = Registry::getLogger();
        /**
         * @var $oPaymentGateway PaymentGateway
         */
        $oPaymentGateway = oxNew(PaymentGateway::class);
        $oPayment = $oOrder->getOrderPayment();
        $oPaymentMethod = $oPayment->getPaymentMethod();
        $oResponse = null;

        try {
            $oPaymentMethod->onBeforeTransactionCreation();
        } catch (Exception $oException) {
            OrderHelper::setSessionPaymentError($oException->getMessage());

            return 'payment';
        }

        try {
            $oTransaction = $oPaymentGateway->createTransaction($oBasket, $oOrder);
            $oResponse = $oPaymentGateway->executeTransaction($oTransaction, $oOrder, $oBasket);
        } catch (\Exception $oException) {
            $oLogger->error(__METHOD__ . ": Error processing transaction: " . $oException->getMessage(), [$oException]);
            $oOrder->handleOrderState(Order::STATE_FAILED);

            return $this->_getNextStep(Order::ORDER_STATE_PAYMENTERROR);
        }

        OrderHelper::handleResponse($oResponse, $oLogger, $oOrder);
        $iSuccess = $oOrder->oxorder__wdoxidee_finalizeorderstate->value;
        return $this->_getNextStep($iSuccess);
    }

    /**
     * Gets the request data for rendering the seamless credit card form.
     *
     * @return string
     *
     * @throws Exception
     *
     * @since 1.0.0
     */
    private function _getCreditCardFormRequestData()
    {
        /**
         * @var $oBasket Basket
         */
        $oBasket = $this->getBasket();
        $oTransaction = $this->createCreditCardTransactionFromBasket($oBasket);

        $oPayment = PaymentMethodHelper::getPaymentById($oBasket->getPaymentId());
        $sPaymentAction = $this->_getPaymentAction($oPayment->oxpayments__wdoxidee_transactionaction->value);
        $sLanguageCode = Registry::getLang()->getLanguageAbbr();

        $oTransactionService = $this->_getTransactionService();

        return $oTransactionService->getCreditCardUiWithData($oTransaction, $sPaymentAction, $sLanguageCode);
    }

    /**
     * Returns the AJAX URL for getting new seamless credit card request data.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getCCRequestDataAjaxLink()
    {
        $urlParameters = ['cl'=>'order', 'fnc'=>'getCreditCardFormRequestDataAjax'];
        $queryParamString = '?' . http_build_query($urlParameters, '', '&amp;');
        return Registry::getConfig()->getSslShopUrl() . $queryParamString;
    }

    /**
     * Makes the request data for rendering the seamless credit card form accessible via an AJAX call.
     *
     * @return null
     *
     * @throws Exception
     *
     * @since 1.0.0
     */
    public function getCreditCardFormRequestDataAjax()
    {
        $aResponse = [
            'requestData' => $this->_getCreditCardFormRequestData(),
        ];

        return Registry::getUtils()->showMessageAndExit(json_encode($aResponse));
    }

    /**
     * Returns the URL for loading the payment page script
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getPaymentPageLoaderScriptUrl()
    {
        $oPayment = $this->getPayment();

        return $oPayment->oxpayments__apiurl_wpp . '/loader/paymentPage.js';
    }

    /**
     * Creates a new transaction object from the current session's basket
     *
     * @param Basket $oBasket
     *
     * @return CreditCardTransaction
     *
     * @since 1.0.0
     * @throws Exception
     */
    public function createCreditCardTransactionFromBasket($oBasket)
    {
        /**
         * @var $oPaymentGateway PaymentGateway
         */
        $oPaymentGateway = oxNew(PaymentGateway::class);

        /**
         * @var $oOrder Order
         */
        $oOrder = oxNew(Order::class);
        $oOrder->oxorder__oxpaymenttype = new Field(CreditCardPaymentMethod::getName());
        $oOrder->createTemp($oBasket, $this->getUser());

        $oTransaction = $oPaymentGateway->createTransaction($oBasket, $oOrder);

        $oTransaction->setAmount(new Amount(
            $oBasket->getPrice()->getBruttoPrice(),
            $oBasket->getBasketCurrency()->name
        ));
        $oTransaction->setConfig($this->_getCreditCardPaymentMethodConfig()->get(CreditCardTransaction::NAME));

        $sModuleToken = PaymentGateway::setAndRetrieveModuleToken($this->getSession());
        $oTransaction->setTermUrl(
            Registry::getConfig()->getCurrentShopUrl() . "index.php?cl=order&" . $sModuleToken
            . Helper::getSidQueryString()
        );

        return $oTransaction;
    }

    /**
     * @return TransactionService
     * @throws Exception
     *
     * @since 1.0.0
     */
    private function _getTransactionService()
    {
        if (is_null($this->_oTransactionService)) {
            $this->_oTransactionService = new TransactionService(
                $this->_getCreditCardPaymentMethodConfig(),
                Registry::getLogger()
            );
        }

        return $this->_oTransactionService;
    }

    /**
     * @return CreditCardPaymentMethod
     * @throws Exception
     *
     * @since 1.0.0
     */
    private function _getCreditCardPaymentMethod()
    {
        if (is_null($this->_oCcPaymentMethod)) {
            $this->_oCcPaymentMethod = new CreditCardPaymentMethod();
        }

        return $this->_oCcPaymentMethod;
    }

    /**
     * @return Config
     *
     * @throws Exception
     *
     * @since 1.0.0
     */
    private function _getCreditCardPaymentMethodConfig()
    {
        if (is_null($this->_oPaymentMethodConf)) {
            $this->_oPaymentMethodConf = $this->_getCreditCardPaymentMethod()->getConfig();
        }

        return $this->_oPaymentMethodConf;
    }

    /**
     * Converts the admin panel payment method action into a seamless
     *
     * @param string $sAction
     *
     * @return string
     *
     * @since 1.0.0
     */
    private function _getPaymentAction($sAction)
    {
        if ($sAction == Operation::PAY) {
            return 'purchase';
        }

        return 'authorization';
    }

    /**
     * Checks if terms consent is needed
     *
     * @return bool
     *
     * @since 1.3.0
     */
    public function isConsentNeeded()
    {
        $sPaymentId = $this->getBasket()->getPaymentId();
        return ($sPaymentId == PayolutionInvoicePaymentMethod::getName() ||
                $sPaymentId == PayolutionBtwobPaymentMethod::getName()) &&
            $this->getPayment()->oxpayments__terms->value;
    }
}
