<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use \Exception;
use \OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\DatabaseProvider;
use \OxidEsales\Eshop\Core\Registry;
use \OxidEsales\Eshop\Core\Field;

use Wirecard\Oxid\Model\Payment_Method;
use \Wirecard\PaymentSdk\Entity\Amount;
use \Wirecard\PaymentSdk\Transaction\Operation;
use \Wirecard\PaymentSdk\Response\SuccessResponse;
use \Wirecard\PaymentSdk\Response\FailureResponse;
use \Wirecard\PaymentSdk\Response\Response;
use \Wirecard\PaymentSdk\BackendService;

use \Wirecard\Oxid\Model\Transaction;

/**
 * Class TransactionHandler
 * @package Wirecard\Oxid\Core
 */
class TransactionHandler
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_oLogger;

    /**
     * TransactionHandler constructor.
     */
    public function __construct()
    {
        $this->_oLogger = Registry::getLogger();
    }

    /**
     * Executes a post-processing action on a transaction.
     *
     * @param Transactio $oParentTransaction
     * @param string     $sActionTitle
     * @param float      $fAmount
     *
     * @return array either [status => success] or [status => error, message => errorMessage]
     */
    public function processAction(
        Transaction $oParentTransaction,
        string $sActionTitle,
        float $fAmount = null
    ): array {
        $oPayment = $this->_getPaymentMethod($oParentTransaction);
        $oConfig = $oPayment->getConfig();
        $oOrder = $oParentTransaction->getTransactionOrder();

        $oTransaction = $this->_getTransactionObject($oPayment, $sActionTitle);

        if (empty($oTransaction)) {
            $this->_oLogger->error("action not implemented", ['no implementation for ' . $sActionTitle]);
            return $this->_getErrorMessage('Action "' . $sActionTitle . '" is not implemented');
        }

        $sParentTransactionId = $oParentTransaction->wdoxidee_ordertransactions__transactionid->value;
        $oTransaction->setParentTransactionId($sParentTransactionId);

        if (!is_null($fAmount)) {
            $sCurrencyName = $oOrder->oxorder__oxcurrency->value;
            $oCurrency = Registry::getConfig()->getCurrencyObject($sCurrencyName);

            $oTransaction->setAmount(new Amount(
                Registry::getUtils()->fRound($fAmount, $oCurrency),
                $sCurrencyName
            ));
        }

        // use the backend service to process the action
        $oBackendService = new BackendService($oConfig, $this->_oLogger);
        $oResponse = $oBackendService->process($oTransaction, $sActionTitle);

        return $this->_handleProcessActionResponse($oResponse, $oOrder, $oBackendService);
    }

    /**
     * Handles the success and error response from the post-processing action.
     *
     * @param Response       $oResponse
     * @param Order          $oOrder
     * @param BackendService $oBackendService
     *
     * @return array either [status => success] or [status => error, message => errorMessage]
     */
    private function _handleProcessActionResponse($oResponse, $oOrder, $oBackendService)
    {
        if ($oResponse instanceof FailureResponse) {
            $sErrors = '';

            foreach ($oResponse->getStatusCollection()->getIterator() as $oItem) {
                $sErrors .= $oItem->getDescription() . "<br>";
                $this->_oLogger->error($oItem->getCode() . ': ' . $oItem->getDescription());
            }

            return $this->_getErrorMessage(Helper::translate('text_generic_error') . '<br>' . $sErrors);
        }

        if ($oResponse instanceof SuccessResponse) {
            // create new transaction entry in the database
            $this->_handleNotificationSuccess($oBackendService, $oResponse);

            // also update the order state in the database
            $sUpdatedOrderState = $oBackendService->getOrderState($oResponse->getTransactionType());
            $oOrder->oxorder__wdoxidee_orderstate = new Field($sUpdatedOrderState);
            $oOrder->save();
        }

        return $this->_getSuccessMessage();
    }

    /**
     * Returns the appropriate transaction object for the desired operation.
     *
     * @param Payment_Method $oPayment
     * @param string         $sActionTitle
     *
     * @return \Wirecard\PaymentSdk\Transaction\Transaction
     */
    private function _getTransactionObject($oPayment, $sActionTitle)
    {
        switch ($sActionTitle) {
            case Operation::CANCEL:
                return $oPayment->getCancelTransaction();
            case Operation::PAY:
                return $oPayment->getCaptureTransaction();
        }

        return null;
    }

    /**
     * Handles the success response from the post-processing operation.
     * A new transaction entry is added to the database.
     *
     * @param BackendService  $oBackendService
     * @param SuccessResponse $oResponse
     *
     * @return null
     */
    private function _handleNotificationSuccess($oBackendService, $oResponse)
    {
        $aData = $oResponse->getData();
        $oUtilsDate = Registry::getUtilsDate();
        $sConvertedTimestamp = $oUtilsDate->formatDBTimestamp($oUtilsDate->formTime($aData['completion-time-stamp']));

        // find root transaction ID
        $sRootTransactionId = $this->_getRootTransactionId($oResponse->getParentTransactionId());

        $oOrder = oxNew(Order::class);
        if (!$oOrder->loadWithTransactionId($sRootTransactionId)) {
            $this->_oLogger->error('No order found for transactionId: ' . $oResponse->getParentTransactionId());
            return;
        }

        // transaction amount
        $fAmount = $oResponse->getRequestedAmount()->getValue();

        // determine if transaction state is closed
        $sTransactionState = $oBackendService->isFinal($oResponse->getTransactionType()) ?
                Transaction::STATE_CLOSED : $aData['transaction-state'];

        // create an array with the properties to be saved in the transaction database entry
        $aTransactionProps = array(
            'ordernumber' => $oOrder->oxorder__oxordernr->value,
            'orderid' => $oOrder->oxorder__oxid->value,
            'transactionid' => $oResponse->getTransactionId(),
            'parenttransactionid' => $oResponse->getParentTransactionId(),
            'requestid' => $oResponse->getRequestId(),
            'action' => $oOrder->getOrderPayment()->oxpayments__wdoxidee_transactionaction->value,
            'type' => $oResponse->getTransactionType(),
            'state' => $sTransactionState,
            'amount' => $fAmount,
            'currency' => $oResponse->getRequestedAmount()->getCurrency(),
            'responsexml' => base64_encode($oResponse->getRawData()),
            'date' => $sConvertedTimestamp
        );

        // add a transaction database entry from properties array
        Transaction::createDbEntryFromArray($aTransactionProps);

        // check if the parent transaction needs to be set to 'closed'
        $this->updateParentTransactionStateIfNecessary($oResponse);
    }

    /**
     * Checks if the parent transaction still has money to be captured.
     * If no money is available anymore, the state is set to closed.
     *
     * @param SuccessResponse $oResponse
     */
    public function updateParentTransactionStateIfNecessary($oResponse)
    {
        $sParentTransactionId = $oResponse->getParentTransactionId();

        $oParentTansaction = oxNew(Transaction::class);
        $oParentTansaction->loadWithTransactionId($sParentTransactionId);

        $fRestAmount = $this->getTransactionMaxAmount($sParentTransactionId);

        if ($fRestAmount <= 0) {
            // set transaction state to closed
            $oParentTansaction->wdoxidee_ordertransactions__state = new Field(Transaction::STATE_CLOSED);
            $oParentTansaction->save();
        }
    }

    /**
     * Calculates the maximum amount available for a transaction.
     *
     * @param string $sTransactionId
     *
     * @return float
     */
    public function getTransactionMaxAmount(string $sTransactionId): float
    {
        $oTransaction = oxNew(Transaction::class);
        $oTransaction->loadWithTransactionId($sTransactionId);
        $fBaseAmount = $oTransaction->wdoxidee_ordertransactions__amount->value;

        // get the child transactions amount summed
        $oDb = DatabaseProvider::getDb();
        $oDb->setFetchMode(DatabaseProvider::FETCH_MODE_ASSOC);

        $sDbIdentifier = $oDb->quoteIdentifier('wdoxidee_ordertransactions');

        $sDbQuery = "SELECT SUM(amount) AS childTransactionsTotalAmount FROM {$sDbIdentifier}
                        WHERE PARENTTRANSACTIONID LIKE ?";

        $aQueryArgs = array($sTransactionId);

        $aResult = $oDb->select($sDbQuery, $aQueryArgs);

        if ($aResult !== false && $aResult->count() > 0) {
            $fChildAmount = $aResult->fields['childTransactionsTotalAmount'];
        }

        return $fBaseAmount - $fChildAmount;
    }

    /**
     * Recursively calls itself until a transaction without a parent transaction ID is found.
     * This is the root transaction and its transaction ID is returned.
     *
     * @param string $sTransactionId
     *
     * @return string root transaction ID
     */
    private function _getRootTransactionId($sTransactionId)
    {
        // get transaction to this transaction ID
        $oTransaction = oxNew(Transaction::class);
        $oTransaction->loadWithTransactionId($sTransactionId);

        $sParentTransactionId = $oTransaction->wdoxidee_ordertransactions__parenttransactionid->value;

        // base case - a transaction without a parent transaction ID was found, this is the root transaction
        if (empty($sParentTransactionId)) {
            return $oTransaction->wdoxidee_ordertransactions__transactionid->value;
        }

        // continue with the parent transaction ID
        return $this->_getRootTransactionId($sParentTransactionId);
    }

    /**
     * Uses the Payment_Method_Factory to create a new Payment_Method object for the desired payment method.
     *
     * @param Transaction $oTransaction
     *
     * @return array|Payment_Method
     */
    private function _getPaymentMethod(Transaction $oTransaction)
    {
        try {
            return Payment_Method_Factory::create($oTransaction->getPaymentType());
        } catch (Exception $oExc) {
            $this->_oLogger->error("Error getting the payment method", [$oExc]);
            return $this->_getErrorMessage($oExc->getMessage());
        }
    }

    /**
     * Returns a success message after fulfilling a post-processing operation.
     *
     * @return array
     */
    private function _getSuccessMessage()
    {
        return ['status' => Transaction::STATE_SUCCESS];
    }

    /**
     * Returns a error message after fulfilling a post-processing operation.
     *
     * @param string $sMessage
     *
     * @return array
     */
    private function _getErrorMessage(string $sMessage): array
    {
        return ['status' => Transaction::STATE_ERROR, 'message' => $sMessage];
    }
}
