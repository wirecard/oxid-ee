<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend\Controller;

use \OxidEsales\Eshop\Core\Field;
use \OxidEsales\Eshop\Core\Registry;
use \OxidEsales\Eshop\Application\Model\Basket;
use \OxidEsales\Eshop\Core\Exception\ArticleInputException;
use \OxidEsales\Eshop\Core\Exception\NoArticleException;
use \OxidEsales\Eshop\Core\Exception\OutOfStockException;
use \OxidEsales\Eshop\Application\Model\User;

use \Wirecard\PaymentSdk\Response\FailureResponse;
use \Wirecard\PaymentSdk\Response\InteractionResponse;
use \Wirecard\PaymentSdk\Response\Response;
use \Wirecard\PaymentSdk\Entity\Status;

use \Wirecard\Oxid\Extend\Model\Payment_Gateway;
use \Wirecard\Oxid\Extend\Model\Order;
use \Wirecard\Oxid\Extend\Model\Payment;

use \Psr\Log\LoggerInterface;

/**
 * Class Order
 *
 * @mixin \OxidEsales\Eshop\Application\Controller\OrderController
 */
class OrderController extends OrderController_parent
{
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

        if ($this->_isPaymentFinished($sWdSessionToken, $sWdPaymentRedirect)) {
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $oBasket = $this->getSession()->getBasket();
        $oPayment = oxNew(Payment::class);
        $oPayment->load($oBasket->getPaymentId());

        if (!$oPayment->isCustomPaymentMethod()) {
            return parent::execute();
        }

        $oOrder = oxNew(Order::class);
        $sOrderId = Registry::getSession()->getVariable('sess_challenge');
        $bIsOrderLoaded = $oOrder->load($sOrderId);
        if ($bIsOrderLoaded) {
            $iSuccess = $oOrder->oxorder__wdoxidee_finalizeorderstate->value;
        }

        $oConfig = Registry::getConfig();
        $sWdPaymentRedirect = $oConfig->getRequestParameter('wdtoken');
        $sWdSessionToken = $this->getSession()->getVariable('wdtoken');
        if ($this->_isPaymentFinished($sWdSessionToken, $sWdPaymentRedirect)) {
            return $this->_getNextStep($iSuccess);
        }

        // delete old order if payment was canceled
        if ($bIsOrderLoaded) {
            $oOrder->delete($sOrderId);
        }

        $oUser = $this->getUser();

        if (!$this->_shouldCreateOrder($oUser)) {
            return 'payment?payerror=2';
        }

        if ($oBasket->getProductsCount()) {
            $oOrder = $this->_createOrder($oBasket, $oUser);

            if (!$oOrder) {
                $iSuccess = $oOrder->oxorder__wdoxidee_finalizeorderstate->value;
                return $this->_getNextStep($iSuccess);
            }

            $this->_handleTransaction($oBasket, $oOrder);
        }

        //redirect happens before the return
        return '';
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
     * Create Order
     *
     * @param Basket $oBasket
     * @param User   $oUser
     *
     * @return Order
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function _createOrder($oBasket, $oUser)
    {
        $oOrder = null;

        try {
            $oOrder = oxNew(Order::class);

            //finalizing ordering process (validating, storing order into DB, executing payment, setting status ...)
            $iSuccess = $oOrder->finalizeOrder($oBasket, $oUser);
            $oOrder->oxorder__wdoxidee_finalizeorderstate = new Field($iSuccess);
            $oOrder->save();

            // performing special actions after user finishes order (assignment to special user groups)
            $oUser->onOrderExecute($oBasket, $iSuccess);

            if (!$this->_isFinalizeOrderSuccessful($iSuccess)) {
                return null;
            }
        } catch (OutOfStockException $oEx) {
            $oEx->setDestination('basket');
            Registry::getUtilsView()->addErrorToDisplay($oEx, false, true, 'basket');
        } catch (NoArticleException $oEx) {
            Registry::getUtilsView()->addErrorToDisplay($oEx);
        } catch (ArticleInputException $oEx) {
            Registry::getUtilsView()->addErrorToDisplay($oEx);
        }

        return $oOrder;
    }

    /**
     * Handles Wirecard payment transaction
     *
     * @param Basket $oBasket
     * @param Order  $oOrder
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    private function _handleTransaction($oBasket, $oOrder)
    {
        $oLogger = Registry::getLogger();
        $oPaymentGateway = oxNew(Payment_Gateway::class);
        $oResponse = null;

        try {
            $oResponse = $oPaymentGateway->makeTransaction($oBasket->getPrice()->getBruttoPrice(), $oOrder);
        } catch (\Exception $exc) {
            $oLogger->error("Error processing transaction", [$exc]);
            return;
        }

        $this->_handleResponse($oResponse, $oLogger);
    }

    /**
     * Handle transaction response
     *
     * @param Response        $oResponse
     * @param LoggerInterface $oLogger
     *
     * @return void
     */
    private function _handleResponse($oResponse, $oLogger)
    {
        if ($oResponse instanceof FailureResponse) {
            $oLogger->error('Error processing transaction:');

            foreach ($oResponse->getStatusCollection() as $oStatus) {
                /**
                 * @var Status $oStatus
                 */
                $sSeverity = ucfirst($oStatus->getSeverity());
                $sCode = $oStatus->getCode();
                $sDescription = $oStatus->getDescription();
                $oLogger->error("\t$sSeverity with code $sCode and message '$sDescription' occurred.");
            }
            return;
        }
        $sPageUrl = null;
        if ($oResponse instanceof InteractionResponse) {
            $sPageUrl = $oResponse->getRedirectUrl();
        }

        Registry::getUtils()->redirect($sPageUrl);
    }

    /**
     * Compares the token and redirect to evaluate if the payment is already finished
     *
     * @param mixed $oSessionToken
     * @param mixed $oPaymentRedirect
     *
     * @return bool
     */
    private function _isPaymentFinished($oSessionToken, $oPaymentRedirect)
    {
        return !empty($oSessionToken) && $oSessionToken == $oPaymentRedirect;
    }

    /**
     * Checks if the returned order creation state is one of the success states
     *
     * @param int $iSuccess
     * @return bool
     */
    private function _isFinalizeOrderSuccessful($iSuccess)
    {
        return $iSuccess === Order::ORDER_STATE_MAILINGERROR || $iSuccess === Order::ORDER_STATE_OK;
    }
}
