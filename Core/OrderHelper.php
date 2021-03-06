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

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\OutOfStockException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;

use Psr\Log\LoggerInterface;

use Wirecard\Oxid\Extend\Model\Order;
use Wirecard\Oxid\Extend\Model\Payment;
use Wirecard\Oxid\Model\FormInteractionResponseFields;
use Wirecard\Oxid\Model\PaymentInAdvancePaymentInformation;
use Wirecard\Oxid\Model\PaymentMethod\PaymentInAdvancePaymentMethod;

use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Entity\Status;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\Response;

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
     * @param string $sSepaMandate
     *
     * @return Order
     *
     * @since 1.0.0
     */
    public static function createOrder($oBasket, $oUser, $sSepaMandate = null)
    {
        $oOrder = null;

        try {
            $oOrder = oxNew(Order::class);

            //finalizing ordering process (validating, storing order into DB, executing payment, setting status ...)
            $iSuccess = $oOrder->finalizeOrder($oBasket, $oUser);
            $oOrder->oxorder__wdoxidee_finalizeorderstate = new Field($iSuccess);
            self::_addSepaMandateToOrder($oOrder, $sSepaMandate);

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
     * Adds SEPA Mandate to order
     *
     * @param object $oOrder
     * @param string $sSepaMandate
     *
     * @since 1.0.0
     *
     * @throws Exception
     */
    private static function _addSepaMandateToOrder(&$oOrder, $sSepaMandate)
    {
        if ($sSepaMandate) {
            $oOrder->oxorder__wdoxidee_sepamandate = new Field($sSepaMandate);
        }
    }

    /**
     * Handle transaction response
     *
     * @param Response            $oResponse
     * @param LoggerInterface     $oLogger
     * @param Order               $oOrder
     * @param BackendService|null $oBackendService
     *
     * @return string|null
     *
     * @since 1.0.0
     *
     * @throws Exception
     */
    public static function handleResponse($oResponse, $oLogger, $oOrder, $oBackendService = null)
    {
        if ($oResponse instanceof FailureResponse) {
            return self::_handleFailureResponse($oResponse, $oLogger, $oOrder);
        }

        self::_managePiaPaymentInformation($oResponse, $oOrder);
        self::_handleSaveCredentials($oOrder);

        // set the transaction ID on the order
        $oOrder->oxorder__wdoxidee_transactionid = new Field($oResponse->getTransactionId());
        $oOrder->save();

        if ($oResponse instanceof FormInteractionResponse) {
            return self::_handleFormInteractionResponse($oResponse);
        }

        if ($oResponse instanceof InteractionResponse) {
            return self::_handleInteractionResponse($oResponse);
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
     * @since 1.1.0
     */
    public static function updateOrderState($oOrder, $oResponse, $oBackendService)
    {
        $sUpdatedOrderState = $oBackendService->getOrderState($oResponse->getTransactionType());
        $oOrder->oxorder__wdoxidee_orderstate = new Field($sUpdatedOrderState);
        $oOrder->save();
    }

    /**
     * Manages Pia Payment Information, for the later use on Thank You page
     *
     * @param Response $oResponse
     * @param Order    $oOrder
     *
     * @since 1.3.0
     */
    private static function _managePiaPaymentInformation($oResponse, $oOrder)
    {
        if ($oOrder->oxorder__oxpaymenttype->value === PaymentInAdvancePaymentMethod::getName()) {
            $oResponseXml = simplexml_load_string($oResponse->getRawData());

            $oSession = Registry::getSession();
            $oSession->setVariable(
                PaymentInAdvancePaymentInformation::PIA_PAYMENT_INFORMATION,
                new PaymentInAdvancePaymentInformation(
                    $oResponse->getRequestedAmount()->getValue() . ' ' .
                    $oResponse->getRequestedAmount()->getCurrency(),
                    (string) $oResponseXml->{'merchant-bank-account'}->{'iban'},
                    (string) $oResponseXml->{'merchant-bank-account'}->{'bic'},
                    (string) $oResponseXml->{'provider-transaction-reference-id'}
                )
            );
        }
    }

    /**
     * Saves one-click save checkbox value if needed
     *
     * @param Order $oOrder
     *
     * @since 1.3.0
     */
    private static function _handleSaveCredentials($oOrder)
    {
        if (!$oOrder->oxorder__wdoxidee_transactionid->value) {
            $oOrder->oxorder__wdoxidee_savepaymentcredentials =
                new Field(Registry::getRequest()->getRequestParameter('wdsavecheckbox'));
        }
    }

    /**
     * Handle transaction failure response
     *
     * @param Response        $oResponse
     * @param LoggerInterface $oLogger
     * @param Order           $oOrder
     *
     * @return null
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
        self::setSessionPaymentError(join('<br/>', $aErrorDescriptions));
        $sRedirectUrl = Registry::getConfig()->getShopHomeUrl() . 'cl=payment';

        $oOrder->handleOrderState(Order::STATE_FAILED);

        return Registry::getUtils()->redirect($sRedirectUrl);
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

        $oConfig = $oPaymentMethod->getConfig();

        $oLogger = Registry::getLogger();

        $oBackendService = new BackendService($oConfig, $oLogger);

        $oResponse = $oBackendService->handleResponse($oResponse);

        self::handleResponse($oResponse, $oLogger, $oOrder, $oBackendService);
    }

    /**
     * Handle transaction form interaction response
     *
     * @param FormInteractionResponse $oResponse
     *
     * @return null
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

        return Registry::getUtils()->redirect(
            Registry::getConfig()->getShopUrl() . "index.php?cl=wcpg_form_interaction" . $sSid
        );
    }

    /**
     * Handle transaction interaction response
     *
     * @param InteractionResponse $oResponse
     *
     * @return null
     *
     * @since 1.0.0
     */
    private static function _handleInteractionResponse($oResponse)
    {
        $sPageUrl = $oResponse->getRedirectUrl();

        // do NOT add the "redirected" query parameter here as some payment methods might not work if it is present
        return Registry::getUtils()->redirect($sPageUrl, false);
    }

    /**
     * Sets a payment error text to the session.
     *
     * @param string $sText
     *
     * @since 1.1.0
     */
    public static function setSessionPaymentError($sText)
    {
        $oSession = Registry::getSession();

        $oSession->setVariable(self::PAY_ERROR_VARIABLE, self::PAY_ERROR_ID);
        $oSession->setVariable(self::PAY_ERROR_TEXT_VARIABLE, $sText);
    }

    /**
     * Loads order with session challenge
     *
     * @param Order $oOrder
     *
     * @return bool
     *
     * @since 1.2.0
     */
    public static function loadOrderWithSessionChallenge(&$oOrder)
    {
        $sOrderId = Helper::getSessionChallenge();
        return $oOrder->load($sOrderId);
    }

    /**
     * Runs the payment method's `onBeforeOrderCreation` callback and shows a potential error message to the user.
     *
     * @param Payment $oPayment
     *
     * @return bool false if an error occurred, otherwise true
     *
     * @since 1.2.0
     */
    public static function onBeforeOrderCreation($oPayment)
    {
        if (!$oPayment || !$oPayment->isCustomPaymentMethod()) {
            return true;
        }

        $oSession = Registry::getSession();

        try {
            $oPaymentMethod = PaymentMethodFactory::create($oSession->getBasket()->getPaymentId());
            $oPaymentMethod->onBeforeOrderCreation();
        } catch (Exception $oException) {
            self::setSessionPaymentError($oException->getMessage());

            $sRedirectUrl = Registry::getConfig()->getShopHomeUrl() . 'cl=payment';
            Registry::getUtils()->redirect($sRedirectUrl);

            return false;
        }

        return true;
    }

    /**
     * Get the last order's shipping address for the given user
     *
     * @param string $sUserId
     *
     * @return array|null
     *
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     *
     * @since 1.3.0
     */
    public static function getLastOrderShippingAddress($sUserId)
    {
        $oOrder = oxNew(Order::class);

        if (!$oOrder->load(self::_getLastOrderIdFromDb($sUserId))) {
            return null;
        };

        if (!empty($oOrder->oxorder__oxdelcountryid->value)) {
            return self::_getDeliveryAddressFromOrder($oOrder);
        }

        return self::_getBillingAddressFromOrder($oOrder);
    }

    /**
     * @param string $sUserId
     *
     * @return false|string
     *
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     *
     * @since 1.3.0
     */
    private static function _getLastOrderIdFromDb($sUserId)
    {
        $sQuery = "SELECT `OXID` from oxorder WHERE `OXUSERID`=? ORDER BY `OXORDERDATE` DESC LIMIT 1";
        return DatabaseProvider::getDb()->getOne($sQuery, [$sUserId]);
    }

    /**
     * Get the current shipping address selected for the order
     *
     * @return array
     *
     * @since 1.3.0
     */
    public static function getSelectedShippingAddress()
    {
        $oOrder = oxNew(Order::class);
        $oCurrentAddress =  $oOrder->getDelAddressInfo();

        if (!is_null($oCurrentAddress)) {
            return self::_getAddressFromObject($oCurrentAddress, 'oxaddress');
        }

        $oUser = Registry::getSession()->getUser();
        return self::_getAddressFromObject($oUser, 'oxuser');
    }

    /**
     * Returns the delivery address from the order object
     *
     * @param object $oOrder
     *
     * @return array
     *
     * @since 1.3.0
     */
    private static function _getDeliveryAddressFromOrder($oOrder)
    {
        return self::_getAddressFromOrder($oOrder, 'del');
    }

    /**
     * Returns the billing address from the order object
     *
     * @param object $oOrder
     *
     * @return array
     *
     * @since 1.3.0
     */
    private static function _getBillingAddressFromOrder($oOrder)
    {
        return self::_getAddressFromOrder($oOrder, 'bill');
    }

    /**
     * Returns an address array from the given order object
     *
     * @param object $oOrder
     * @param string $sColumnPrefix
     *
     * @return array
     *
     * @since 1.3.0
     */
    private static function _getAddressFromOrder($oOrder, $sColumnPrefix)
    {
        return self::_getAddressFromObject($oOrder, 'oxorder', $sColumnPrefix);
    }

    /**
     * Returns an address array from the given address object
     *
     * @param object $oDbObject
     * @param string $sTableName
     * @param string $sColumnPrefix
     *
     * @return array
     *
     * @since 1.3.0
     */
    private static function _getAddressFromObject($oDbObject, $sTableName, $sColumnPrefix = '')
    {
        $sDbPrefix = $sTableName . '__ox' . $sColumnPrefix;

        return [
            'first_name' => $oDbObject->{$sDbPrefix . 'fname'}->value,
            'last_name' => $oDbObject->{$sDbPrefix . 'lname'}->value,
            'company' => $oDbObject->{$sDbPrefix . 'company'}->value,
            'street' => $oDbObject->{$sDbPrefix . 'street'}->value,
            'street_nr' => $oDbObject->{$sDbPrefix . 'streetnr'}->value,
            'zip' => $oDbObject->{$sDbPrefix . 'zip'}->value,
            'city' => $oDbObject->{$sDbPrefix . 'city'}->value,
            'country_id' => $oDbObject->{$sDbPrefix . 'countryid'}->value,
            'state_id' => $oDbObject->{$sDbPrefix . 'stateid'}->value,
        ];
    }
}
