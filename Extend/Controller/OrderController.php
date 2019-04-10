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
use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Core\OrderHelper;
use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Extend\Model\Order;
use Wirecard\Oxid\Extend\Model\Payment;
use Wirecard\Oxid\Extend\Model\PaymentGateway;
use Wirecard\Oxid\Model\CreditCardPaymentMethod;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\TransactionService;

/**
 * Class Order
 *
 * @mixin \OxidEsales\Eshop\Application\Controller\OrderController
 */
class OrderController extends OrderController_parent
{
    const FORM_POST_VARIABLE = 'formPost';
    /**
     * @var TransactionService
     */
    private $_oTransactionService;

    /**
     * @var CreditCardPaymentMethod
     */
    private $_oCreditCardPaymentMethod;

    /**
     * @var Config
     */
    private $_oPaymentMethodConfig;

    /**
     * Extends the parent init function and finalizes the order in case it was a Wirecard payment method
     */
    public function init()
    {
        parent::init();

        $oConfig = Registry::getConfig();
        $sWdPaymentRedirect = $oConfig->getRequestParameter('wdpayment');
        $oSession = Registry::getSession();
        $sWdSessionToken = $oSession->getVariable('wdtoken');

        if (OrderHelper::isPaymentFinished($sWdSessionToken, $sWdPaymentRedirect)) {
            $sShopBaseUrl = $oConfig->getShopUrl();
            $sLanguageCode = Registry::getLang()->getBaseLanguage();

            $aParams = array(
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
                'wdtoken' => $sWdSessionToken
            );

            if ($oConfig->getRequestParameter('redirectFromForm')) {
                $oSession->setVariable(self::FORM_POST_VARIABLE, $_POST);
            }

            $sParamStr = http_build_query($aParams);

            $sNewUrl = $sShopBaseUrl . 'index.php?' . $sParamStr;
            Registry::getUtils()->redirect($sNewUrl, false);
        }
    }

    /**
     * Redirects to the PaymentSDK if the payment is a Module's method
     *
     * @inheritdoc
     *
     * @return string
     *
     * @throws \Exception
     */
    public function execute()
    {
        $oSession = Registry::getSession();
        $oBasket = $oSession->getBasket();

        $oPayment = oxNew(Payment::class);
        $oPayment->load($oBasket->getPaymentId());

        if (!$oPayment->isCustomPaymentMethod()) {
            return parent::execute();
        }

        $oOrder = oxNew(Order::class);
        $sOrderId = Helper::getSessionChallenge();
        $bIsOrderLoaded = $oOrder->load($sOrderId);

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
     */
    private function _processOrderTransaction($oOrder, $oBasket, $oUser)
    {
        // check if order should be created first
        if (!$this->_shouldCreateOrder($oUser)) {
            return 'payment?payerror=2';
        }

        if ($oBasket->getProductsCount()) {
            $oOrder = OrderHelper::createOrder($oBasket, $oUser);

            if (!$oOrder) {
                $iSuccess = $oOrder->oxorder__wdoxidee_finalizeorderstate->value;
                return $this->_getNextStep($iSuccess);
            }

            $this->_handleTransaction($oBasket, $oOrder);
        }

        $iSuccess = $oOrder->oxorder__wdoxidee_finalizeorderstate->value;
        return $this->_getNextStep($iSuccess);
    }

    /**
     * Makes multiple checks if the order should be created
     *
     * @param User $oUser
     *
     * @return bool
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
     * Handles Wirecard payment transaction
     *
     * @param Basket $oBasket
     * @param Order  $oOrder
     *
     * @return void
     */
    private function _handleTransaction($oBasket, $oOrder)
    {
        $oLogger = Registry::getLogger();
        /**
         * @var $oPaymentGateway PaymentGateway
         */
        $oPaymentGateway = oxNew(PaymentGateway::class);
        $oResponse = null;

        try {
            $oTransaction = $oPaymentGateway->createTransaction($oBasket, $oOrder);
            $oResponse = $oPaymentGateway->executeTransaction($oTransaction, $oOrder, $oBasket);
        } catch (\Exception $exc) {
            $oLogger->error(__METHOD__ . ": Error processing transaction: " . $exc->getMessage(), [$exc]);
            return;
        }

        OrderHelper::handleResponse($oResponse, $oLogger, $oOrder);
    }

    /**
     *
     * Returns the parameters used to render the credit card form
     *
     * @return string
     * @throws Exception
     */
    public function getInitCreditCardFormJavaScript(): string
    {

        /**
         * @var $oPaymentGateway PaymentGateway
         */
        $oPaymentGateway = oxNew(PaymentGateway::class);

        /**
         * @var $oBasket Basket
         */
        $oBasket = $this->getBasket();

        /**
         * @var $oOrder Order
         */
        $oOrder = oxNew(Order::class);
        $oOrder->createTemp($oBasket, $this->getUser());

        $oTransaction = $oPaymentGateway->createTransaction($oBasket, $oOrder);

        $oTransaction->setAmount(new Amount(
            $oBasket->getPrice()->getBruttoPrice(),
            $oBasket->getBasketCurrency()->name
        ));
        $oTransaction->setConfig($this->_getCreditCardPaymentMethodConfig()->get(CreditCardTransaction::NAME));

        $oSession = $this->getSession();
        $sSid = Helper::getSidQueryString();

        $sModuleToken = PaymentGateway::getModuleToken($oSession);
        $oTransaction->setTermUrl(
            Registry::getConfig()->getCurrentShopUrl() . "index.php?cl=order&" . $sModuleToken . $sSid
        );

        $oTransactionService = $this->_getTransactionService();

        $oPayment = oxNew(Payment::class);
        $oPayment->load($oBasket->getPaymentId());

        // This string is used in out/blocks/wirecard_credit_card_fields.tpl to render the form
        return "ModuleCreditCardForm.init(" .
            $oTransactionService->getCreditCardUiWithData(
                $oTransaction,
                $this->_getPaymentAction($oPayment->oxpayments__wdoxidee_transactionaction->value),
                Registry::getLang()->getLanguageAbbr()
            ) . ")";
    }

    /**
     * @return TransactionService
     * @throws Exception
     */
    private function _getTransactionService(): TransactionService
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
     */
    private function _getCreditCardPaymentMethod(): CreditCardPaymentMethod
    {
        if (is_null($this->_oCreditCardPaymentMethod)) {
            $this->_oCreditCardPaymentMethod = new CreditCardPaymentMethod();
        }

        return $this->_oCreditCardPaymentMethod;
    }

    /**
     * @return Config
     * @throws Exception
     */
    private function _getCreditCardPaymentMethodConfig(): Config
    {
        if (is_null($this->_oPaymentMethodConfig)) {
            $oPayment = $this->getPayment();
            $this->_oPaymentMethodConfig = $this->_getCreditCardPaymentMethod()->getConfig($oPayment);
        }

        return $this->_oPaymentMethodConfig;
    }

    /**
     * Converts the admin panel payment method action into a seamless
     *
     * @param string $sAction
     *
     * @return string
     */
    private function _getPaymentAction(string $sAction): string
    {
        if ($sAction == Operation::PAY) {
            return 'purchase';
        }
        return 'authorization';
    }
}
