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
use OxidEsales\Eshop\Core\Exception\SystemComponentException;

use Psr\Log\LoggerInterface;

use Wirecard\Oxid\Model\Transaction;
use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\Oxid\Extend\Model\Order;
use Wirecard\PaymentSdk\Response\SuccessResponse;

/**
 * Class ResponseHandler
 *
 * @since 1.0.0
 */
class ResponseHandler
{
    /**
     * Handles success notifications
     *
     * @param SuccessResponse $oResponse
     * @param BackendService  $oBackendService
     * @param Order           $oOrder
     *
     * @throws \Exception
     *
     * @since 1.0.0
     */
    public static function onSuccessResponse($oResponse, $oBackendService, $oOrder)
    {
        /**
         * @var $oLogger LoggerInterface
         */
        $oLogger = Registry::getLogger();

        $oLogger->debug('Success response: ' . $oResponse->getRawData());

        //if (!$oResponse->isValidSignature()) {
        //    $oLogger->warning('Transaction was possibly manipulated');
        //}

        self::_saveTransaction($oResponse, $oOrder, $oBackendService);

        if (!self::_isPostProcessingAction($oResponse)) {
            self::_updateOrder($oOrder, $oResponse, $oBackendService);

            try {
                $oOrder->sendOrderByEmail();
            } catch (Exception $exc) {
                // this error occurrs when the 'Azure' theme is activated and a non-3DS credit card transaction is made
                // everything was actually successful, but the 'getThumbnailUrl' method does not exist on the BasketItem
                // in the 'order_cust.tpl' file. There is no need to do anything in this case but normally continue with
                // the response handling.
                // Otherwise the exception is re-thrown.
                $sAzureErrorMsg = "Function 'getThumbnailUrl' does not exist or is not accessible!";

                if (!($exc instanceof SystemComponentException && strpos($exc->getMessage(), $sAzureErrorMsg) === 0)) {
                    throw $exc;
                }

                $oLogger->debug($exc->getMessage());
            }
        }
    }

    /**
     * Get parent transaction id, returns null if there is no parent transaction in the table
     *
     * @param Response $oResponse
     *
     * @return string
     *
     * @since 1.0.0
     */
    private static function _getParentTransactionId($oResponse)
    {
        $sParentTransactionId = $oResponse->getParentTransactionId();
        $oTransaction = oxNew(Transaction::class);
        if (!$oTransaction->loadWithTransactionId($sParentTransactionId)) {
            return null;
        }

        return $sParentTransactionId;
    }

    /**
     * Determines whether a transaction response belongs to a post processing action.
     * This is determined by whether a parent transaction ID is set on the response or not.
     *
     * @param Response $oResponse
     *
     * @return boolean
     *
     * @since 1.0.1
     */
    private static function _isPostProcessingAction($oResponse)
    {
        return self::_getParentTransactionId($oResponse) !== null;
    }

    /**
     * Creates a new transaction entry in the database.
     *
     * @param SuccessResponse $oResponse
     * @param Order           $oOrder
     * @param BackendService  $oBackendService
     *
     * @throws \Exception
     *
     * @since 1.0.0
     */
    private static function _saveTransaction($oResponse, $oOrder, $oBackendService)
    {
        $aData = $oResponse->getData();
        $oUtilsDate = Registry::getUtilsDate();
        $sConvertedTimestamp = $oUtilsDate->formatDBTimestamp($oUtilsDate->formTime($aData['completion-time-stamp']));

        // determine if transaction state is closed
        $sTransactionState = $oBackendService->isFinal($oResponse->getTransactionType()) ?
            Transaction::STATE_CLOSED : Transaction::STATE_SUCCESS;

        // create an array with the properties to be saved in the transaction database entry
        $aTransactionProps = [
            'ordernumber' => $oOrder->oxorder__oxordernr->value,
            'orderid' => $oOrder->oxorder__oxid->value,
            'transactionid' => $oResponse->getTransactionId(),
            'parenttransactionid' => self::_getParentTransactionId($oResponse),
            'requestid' => $oResponse->getRequestId(),
            'action' => $oOrder->getOrderPayment()->oxpayments__wdoxidee_transactionaction->value,
            'type' => $oResponse->getTransactionType(),
            'state' => $sTransactionState,
            'amount' => $oResponse->getRequestedAmount()->getValue(),
            'currency' => $oResponse->getRequestedAmount()->getCurrency(),
            'responsexml' => base64_encode($oResponse->getRawData()),
            'date' => $sConvertedTimestamp,
        ];

        //$aTransactionProps['validsignature'] = $oResponse->isValidSignature();

        Transaction::createDbEntryFromArray($aTransactionProps);
    }

    /**
     * @param Order           $oOrder
     * @param SuccessResponse $oResponse
     * @param BackendService  $oBackendService
     *
     * @return void
     *
     * @since 1.0.0
     */
    private static function _updateOrder($oOrder, $oResponse, $oBackendService)
    {
        $oOrder->oxorder__wdoxidee_providertransactionid = new Field(
            $oResponse->getData()['statuses.0.provider-transaction-id']
        );
        $oOrder->oxorder__wdoxidee_transactionid = new Field($oResponse->getTransactionId());
        $oOrder->oxorder__oxpaid = new Field(
            Helper::getFormattedDbDate($oResponse->getData()['completion-time-stamp'])
        );
        $oOrder->save();

        if ($oOrder->oxorder__wdoxidee_final->value) {
            Registry::getLogger()->warning(
                'Corresponding order is already finished, nothing updated! OrderId: ' .
                $oOrder->oxorder__oxid->value
            );
            return;
        }

        $oOrder->oxorder__wdoxidee_orderstate
            = new Field($oBackendService->getOrderState($oResponse->getTransactionType()));
        $oOrder->oxorder__wdoxidee_final = new Field(1);
        $oOrder->save();
    }
}
