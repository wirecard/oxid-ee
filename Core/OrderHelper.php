<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use Exception;
use Psr\Log\LoggerInterface;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\ArticleInputException;
use OxidEsales\Eshop\Core\Exception\NoArticleException;
use OxidEsales\Eshop\Core\Exception\OutOfStockException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;

use Wirecard\Oxid\Extend\Model\Order;
use Wirecard\Oxid\Extend\Model\Payment;
use Wirecard\Oxid\Model\FormInteractionResponseFields;
use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Entity\Status;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Response\SuccessResponse;

/**
 * Helper class to handle orders
 *
 * @since 1.0.0
 */
class OrderHelper
{

    const PAY_ERROR_VARIABLE = 'payerror';
    const PAY_ERROR_ID = '-102';
    const PAY_ERROR_TEXT_VARIABLE = 'payerrortext';

    /**
     * Create Order
     *
     * @param Basket $oBasket
     * @param User   $oUser
     *
     * @return Order
     *
     * @since 1.0.0
     */
    public static function createOrder($oBasket, $oUser)
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

            if (!self::isFinalizeOrderSuccessful($iSuccess)) {
                return null;
            }
        } catch (OutOfStockException $oEx) {
            $oEx->setDestination('basket');
            Registry::getUtilsView()->addErrorToDisplay($oEx, false, true, 'basket');
        } catch (Exception $oEx) {
            Registry::getUtilsView()->addErrorToDisplay($oEx);
        }

        return $oOrder;
    }

    /**
     * Handle transaction response
     *
     * @param Response            $oResponse
     * @param LoggerInterface     $oLogger
     * @param Order               $oOrder
     * @param BackendService|null $oBackendService
     *
     * @since 1.0.0
     *
     * @throws Exception
     */
    public static function handleResponse($oResponse, $oLogger, $oOrder, $oBackendService = null)
    {
        if ($oResponse instanceof FailureResponse) {
            self::_handleFailureResponse($oResponse, $oLogger, $oOrder);
        }

        // set the transaction ID on the order
        $oOrder->oxorder__wdoxidee_transactionid = new Field($oResponse->getTransactionId());
        $oOrder->save();

        if ($oResponse instanceof FormInteractionResponse) {
            self::_handleFormInteractionResponse($oResponse);
        }

        if ($oResponse instanceof InteractionResponse) {
            self::_handleInteractionResponse($oResponse);
        }

        self::_onSuccessResponse($oResponse, $oBackendService, $oOrder);
    }

    /**
     * @param Response       $oResponse
     * @param BackendService $oBackendService
     * @param Order          $oOrder
     *
     * @throws Exception
     *
     * @since 1.0.0
     */
    private static function _onSuccessResponse($oResponse, $oBackendService, $oOrder)
    {
        if (!is_null($oBackendService) && $oResponse instanceof SuccessResponse) {
            ResponseHandler::onSuccessResponse($oResponse, $oBackendService, $oOrder);
        }
    }

    /**
     * Compares the token and redirect to evaluate if the payment is already finished
     *
     * @param mixed $oSessionToken
     * @param mixed $oPaymentRedirect
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public static function isPaymentFinished($oSessionToken, $oPaymentRedirect)
    {
        return !empty($oSessionToken) && $oSessionToken == $oPaymentRedirect;
    }

    /**
     * Checks if the returned order creation state is one of the success states
     *
     * @param int $iSuccess
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public static function isFinalizeOrderSuccessful($iSuccess)
    {
        return $iSuccess === Order::ORDER_STATE_MAILINGERROR || $iSuccess === Order::ORDER_STATE_OK;
    }

    /**
     * Uses the backend service to determine the new order state.
     *
     * @param Order          $oOrder
     * @param Response       $oResponse
     * @param BackendService $oBackendService
     *
     * @since 1.0.1
     */
    public static function updateOrderState($oOrder, $oResponse, $oBackendService)
    {
        $sUpdatedOrderState = $oBackendService->getOrderState($oResponse->getTransactionType());
        $oOrder->oxorder__wdoxidee_orderstate = new Field($sUpdatedOrderState);
        $oOrder->save();
    }

    /**
     * Handle transaction failure response
     *
     * @param Response        $oResponse
     * @param LoggerInterface $oLogger
     * @param Order           $oOrder
     *
     * @since 1.0.0
     */
    private static function _handleFailureResponse($oResponse, $oLogger, $oOrder)
    {
        $oLogger->error('Error processing transaction:');

        $aErrorDescriptions = [];

        foreach ($oResponse->getStatusCollection() as $oStatus) {
            /**
             * @var Status $oStatus
             */
            $sSeverity = ucfirst($oStatus->getSeverity());
            $sCode = $oStatus->getCode();
            $sDescription = $oStatus->getDescription();

            // add error message and code to array for display on frontend
            $aErrorDescriptions[] = $sDescription . ' (Error code: ' . $sCode . ')';

            $oLogger->error("\t$sSeverity with code $sCode and message '$sDescription' occurred.");
        }

        // set the custom payment error code and text and redirect back to the payment step of the checkout process
        Registry::getSession()->setVariable(self::PAY_ERROR_VARIABLE, self::PAY_ERROR_ID);
        Registry::getSession()->setVariable(self::PAY_ERROR_TEXT_VARIABLE, join('<br/>', $aErrorDescriptions));
        $sRedirectUrl = Registry::getConfig()->getShopHomeUrl() . 'cl=payment';

        $oOrder->handleOrderState(Order::STATE_FAILED);

        Registry::getUtils()->redirect($sRedirectUrl);
    }

    /**
     * Handles transaction form response
     *
     * @param Session $oSession
     * @param Payment $oPayment
     * @param Order   $oOrder
     * @param string  $sFormPostVariable
     *
     * @throws \Exception
     * @return void
     *
     * @since 1.0.0
     */
    public static function handleFormResponse($oSession, $oPayment, $oOrder, $sFormPostVariable)
    {
        $oResponse = $oSession->getVariable($sFormPostVariable);
        if (empty($oResponse)) {
            return;
        }

        $oSession->deleteVariable($sFormPostVariable);
        $oPaymentMethod = PaymentMethodFactory::create($oPayment->oxpayments__oxid->value);

        $oConfig = $oPaymentMethod->getConfig($oPayment);

        $oLogger = Registry::getLogger();

        $oBackendService = new BackendService($oConfig, $oLogger);

        $oResponse = $oBackendService->handleResponse($oResponse);

        self::handleResponse($oResponse, $oLogger, $oOrder, $oBackendService);
    }

    /**
     * Handle transaction form interaction response
     *
     * @param Response $oResponse
     *
     * @since 1.0.0
     */
    private static function _handleFormInteractionResponse($oResponse)
    {
        /**
         * @var $oSession \OxidEsales\Eshop\Core\Session
         */
        $oSession = Registry::getSession();
        $oSession->setVariable(
            "wdFormInteractionResponse",
            new FormInteractionResponseFields(
                $oResponse->getUrl(),
                $oResponse->getMethod(),
                $oResponse->getFormFields()
            )
        );

        $sSid = Helper::getSidQueryString();

        Registry::getUtils()->redirect(
            Registry::getConfig()->getShopUrl() . "index.php?cl=wcpg_form_interaction" . $sSid
        );
    }

    /**
     * Handle transaction interaction response
     *
     * @param Response $oResponse
     *
     * @since 1.0.0
     */
    private static function _handleInteractionResponse($oResponse)
    {
        $sPageUrl = $oResponse->getRedirectUrl();
        Registry::getUtils()->redirect($sPageUrl);
    }
}
