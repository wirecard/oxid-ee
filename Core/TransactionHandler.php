<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Field;

use Wirecard\Oxid\Model\PaymentMethod;
use Wirecard\Oxid\Model\Transaction;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\BackendService;

use Exception;

/**
 * Class TransactionHandler
 * @mixin Wirecard\Oxid\Core
 *
 * @since 1.0.1
 */
class TransactionHandler
{
    /**
     * @var \Psr\Log\LoggerInterface
     *
     * @since 1.0.1
     */
    private $_oLogger;

    /**
     * @var BackendService
     *
     * @since 1.0.1
     */
    private $_oBackendService;

    /**
     * TransactionHandler constructor.
     *
     * @param BackendService $oBackendService
     *
     * @since 1.0.1
     */
    public function __construct($oBackendService)
    {
        $this->_oLogger = Registry::getLogger();
        $this->_oBackendService = $oBackendService;
    }

    /**
     * Executes a post-processing action on a transaction.
     *
     * @param Transaction $oParentTransaction
     * @param string      $sActionTitle
     * @param float       $fAmount
     *
     * @return array either [status => success] or [status => error, message => errorMessage]
     *
     * @since 1.0.1
     */
    public function processAction($oParentTransaction, $sActionTitle, $fAmount = null)
    {
        $oPaymentMethod = $this->_getPaymentMethod($oParentTransaction, $sActionTitle);
        $oOrder = $oParentTransaction->getTransactionOrder();

        $oTransaction = $oPaymentMethod->getTransaction();

        $sParentTransactionId = $oParentTransaction->wdoxidee_ordertransactions__transactionid->value;
        $oTransaction->setParentTransactionId($sParentTransactionId);

        $oPaymentMethod->addMandatoryTransactionData($oTransaction, $oParentTransaction);

        if (!is_null($fAmount)) {
            $sCurrencyName = $oOrder->oxorder__oxcurrency->value;
            $oCurrency = Registry::getConfig()->getCurrencyObject($sCurrencyName);

            $oTransaction->setAmount(new Amount(
                Registry::getUtils()->fRound($fAmount, $oCurrency),
                $sCurrencyName
            ));
        }

        $oResponse = $this->_oBackendService->process($oTransaction, $sActionTitle);

        return $this->_onActionResponse($oResponse);
    }

    /**
     * Handles the success and error response from the post-processing action.
     *
     * @param Response $oResponse
     *
     * @return array either [status => success] or [status => error, message => errorMessage]
     *
     * @since 1.0.1
     */
    private function _onActionResponse($oResponse)
    {
        if ($oResponse instanceof FailureResponse) {
            return $this->_onActionFailure($oResponse);
        }

        if ($oResponse instanceof SuccessResponse) {
            return $this->_onActionSuccess($oResponse);
        }

        return $this->_getErrorMessage('No handler for this response type implemented');
    }

    /**
     * Handle the success response case.
     *
     * @param SuccessResponse $oResponse
     *
     * @return array
     *
     * @since 1.0.1
     */
    private function _onActionSuccess($oResponse)
    {
        $sRootTransactionId = $this->_getRootTransactionId($oResponse->getParentTransactionId());
        $oOrder = oxNew(Order::class);
        if (!$oOrder->loadWithTransactionId($sRootTransactionId)) {
            $sErrorMessage = 'No order found for transactionId: ' . $oResponse->getParentTransactionId();
            $this->_oLogger->error($sErrorMessage);
            return $this->_getErrorMessage(Helper::translate('wd_text_generic_error') . '<br>' . $sErrorMessage);
        }

        // the reponse handler creates the transaction entry in the database
        ResponseHandler::onSuccessResponse($oResponse, $this->_oBackendService, $oOrder);
        $this->_updateParentTransactionStateIfNecessary($oResponse);
        OrderHelper::updateOrderState($oOrder, $oResponse, $this->_oBackendService);

        return $this->_getSuccessMessage();
    }

    /**
     * Handles the failure response case.
     *
     * @param FailureResponse $oResponse
     *
     * @return array
     *
     * @since 1.0.1
     */
    private function _onActionFailure($oResponse)
    {
        $sErrorDescription = $this->_getErrorString($oResponse);
        return $this->_getErrorMessage(Helper::translate('wd_text_generic_error') . '<br>' . $sErrorDescription);
    }

    /**
     * Iterates over all errors in the FailureResponse and returns the descriptions as a string.
     *
     * @param FailureResponse $oResponse
     *
     * @return string
     *
     * @since 1.0.1
     */
    private function _getErrorString($oResponse)
    {
        $sErrors = '';

        foreach ($oResponse->getStatusCollection()->getIterator() as $oItem) {
            $sErrors .= $oItem->getDescription() . "<br>";
            $this->_oLogger->error($oItem->getCode() . ': ' . $oItem->getDescription());
        }

        return $sErrors;
    }

    /**
     * Checks if the parent transaction still has money to be captured.
     * If no money is available anymore, the state is set to closed.
     *
     * @param SuccessResponse $oResponse
     *
     * @since 1.0.1
     */
    private function _updateParentTransactionStateIfNecessary($oResponse)
    {
        $sParentTransactionId = $oResponse->getParentTransactionId();

        $oParentTransaction = oxNew(Transaction::class);
        $oParentTransaction->loadWithTransactionId($sParentTransactionId);

        $fRestAmount = $this->getTransactionMaxAmount($sParentTransactionId);

        if ($fRestAmount <= 0) {
            $oParentTransaction->wdoxidee_ordertransactions__state = new Field(Transaction::STATE_CLOSED);
            $oParentTransaction->save();
        }
    }

    /**
     * Calculates the maximum amount available for a transaction.
     *
     * @param string $sTransactionId
     *
     * @return float
     *
     * @since 1.0.1
     */
    public function getTransactionMaxAmount($sTransactionId)
    {
        $oTransaction = oxNew(Transaction::class);
        $oTransaction->loadWithTransactionId($sTransactionId);
        $fBaseAmount = $oTransaction->wdoxidee_ordertransactions__amount->value;

        // get the child transactions amount summed
        $oDb = DatabaseProvider::getDb();
        $oDb->setFetchMode(DatabaseProvider::FETCH_MODE_ASSOC);

        $sDbIdentifier = $oDb->quoteIdentifier('wdoxidee_ordertransactions');

        $sDbQuery = "SELECT SUM(amount) AS childTransactionsTotalAmount FROM {$sDbIdentifier}
                        WHERE PARENTTRANSACTIONID = ?";

        $aQueryArgs = [$sTransactionId];

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
     *
     * @since 1.0.1
     */
    private function _getRootTransactionId($sTransactionId)
    {
        $oTransaction = oxNew(Transaction::class);
        $oTransaction->loadWithTransactionId($sTransactionId);

        $sParentTransactionId = $oTransaction->wdoxidee_ordertransactions__parenttransactionid->value;

        // base case - a transaction without a parent transaction ID was found, this is the root transaction
        if (empty($sParentTransactionId)) {
            return $oTransaction->wdoxidee_ordertransactions__transactionid->value;
        }

        return $this->_getRootTransactionId($sParentTransactionId);
    }

    /**
     * Uses the PaymentMethodFactory to create a new (post-processing) PaymentMethod object
     * for the desired payment method.
     *
     * @param Transaction $oTransaction
     * @param string      $sActionTitle
     *
     * @return array|PaymentMethod
     *
     * @since 1.0.1
     */
    private function _getPaymentMethod($oTransaction, $sActionTitle)
    {
        try {
            $oPaymentMethod = PaymentMethodFactory::create($oTransaction->getPaymentType());
            return $oPaymentMethod->getPostProcessingPaymentMethod($sActionTitle);
        } catch (Exception $oExc) {
            $this->_oLogger->error("Error getting the payment method", [$oExc]);
            return $this->_getErrorMessage($oExc->getMessage());
        }
    }

    /**
     * Returns a success message after fulfilling a post-processing operation.
     *
     * @return array
     *
     * @since 1.0.1
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
     *
     * @since 1.0.1
     */
    private function _getErrorMessage($sMessage)
    {
        return ['status' => Transaction::STATE_ERROR, 'message' => $sMessage];
    }
}
