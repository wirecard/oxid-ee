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
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Core\Exception\ArticleInputException;
use OxidEsales\Eshop\Core\Exception\NoArticleException;
use OxidEsales\Eshop\Core\Exception\OutOfStockException;
use OxidEsales\Eshop\Application\Model\User;

use OxidEsales\Eshop\Core\Session;
use Wirecard\Oxid\Model\FormInteractionResponseFields;
use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Entity\Status;
use Wirecard\PaymentSdk\TransactionService;

use \Wirecard\Oxid\Extend\Model\Order;
use \Wirecard\Oxid\Extend\Model\Payment;

use \Psr\Log\LoggerInterface;

/**
 * Helper class to handle orders
 */
class OrderHelper
{
    /**
     * Create Order
     *
     * @param Basket $oBasket
     * @param User   $oUser
     *
     * @return Order
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
        } catch (NoArticleException | ArticleInputException | Exception $oEx) {
            Registry::getUtilsView()->addErrorToDisplay($oEx);
        }

        return $oOrder;
    }

    /**
     * Handle transaction response
     *
     * @param Response        $oResponse
     * @param LoggerInterface $oLogger
     * @param Order           $oOrder
     *
     * @return bool
     */
    public static function handleResponse($oResponse, $oLogger, $oOrder)
    {
        if ($oResponse instanceof FailureResponse) {
            return self::_handleFailureResponse($oResponse, $oLogger, $oOrder);
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

        return true;
    }

    /**
     * Compares the token and redirect to evaluate if the payment is already finished
     *
     * @param mixed $oSessionToken
     * @param mixed $oPaymentRedirect
     *
     * @return bool
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
     */
    public static function isFinalizeOrderSuccessful($iSuccess)
    {
        return $iSuccess === Order::ORDER_STATE_MAILINGERROR || $iSuccess === Order::ORDER_STATE_OK;
    }

    /**
     * Handle transaction failure response
     *
     * @param Response        $oResponse
     * @param LoggerInterface $oLogger
     * @param Order           $oOrder
     *
     * @return bool
     */
    private static function _handleFailureResponse($oResponse, $oLogger, $oOrder)
    {
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
        $oOrder->delete();

        return false;
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
     */
    public static function handleFormResponse($oSession, $oPayment, $oOrder, $sFormPostVariable)
    {
        $oResponse = $oSession->getVariable($sFormPostVariable);
        if (!empty($oResponse)) {
            $oSession->deleteVariable($sFormPostVariable);
            $oPaymentMethod = PaymentMethodFactory::create($oPayment->oxpayments__oxid->value);

            $oConfig = $oPaymentMethod->getConfig($oPayment);

            $oLogger = Registry::getLogger();

            $oTransactionService = new TransactionService($oConfig, $oLogger);

            $oResponse = $oTransactionService->handleResponse($oResponse);

            if (self::handleResponse($oResponse, $oLogger, $oOrder)) {
                ResponseHandler::onSuccessResponse($oResponse, new BackendService($oConfig, $oLogger), $oOrder);
            }
        }
    }

    /**
     * Handle transaction form interaction response
     *
     * @param Response $oResponse
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
     */
    private static function _handleInteractionResponse($oResponse)
    {
        $sPageUrl = $oResponse->getRedirectUrl();
        Registry::getUtils()->redirect($sPageUrl);
    }
}
