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

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Model\Transaction;

use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Response\SuccessResponse;

/**
 * Class TransactionHandler
 * @mixin Wirecard\Oxid\Core
 *
 * @since 1.1.0
 */
class TransactionHandler
{
    /**
     * @var \Psr\Log\LoggerInterface
     *
     * @since 1.1.0
     */
    private $_oLogger;

    /**
     * @var BackendService
     *
     * @since 1.1.0
     */
    private $_oBackendService;

    /**
     * TransactionHandler constructor.
     *
     * @param BackendService $oBackendService
     *
     * @since 1.1.0
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
     * @param float|null  $fAmount
     * @param array|null  $aOrderItems
     *
     * @return array either [status => success] or [status => error, message => errorMessage]
     *
     * @throws Exception
     *
     * @since 1.1.0
     */
    public function processAction($oParentTransaction, $sActionTitle, $fAmount = null, $aOrderItems = null)
    {
        $oOrder = $oParentTransaction->getTransactionOrder();
        $oTransaction = $this->_getPostProcessingTransaction($oParentTransaction, $sActionTitle, $aOrderItems);

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
     * @since 1.1.0
     * @throws Exception
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
     * @throws Exception
     *
     * @since 1.1.0
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
     * @since 1.1.0
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
     * @since 1.1.0
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
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     *
     * @since 1.1.0
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
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     *
     * @since 1.1.0
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

        $sDbQuery = "SELECT SUM(`amount`) AS childTransactionsTotalAmount FROM {$sDbIdentifier}
                        WHERE PARENTTRANSACTIONID = ?";

        $aQueryArgs = [$sTransactionId];

        $aResult = $oDb->select($sDbQuery, $aQueryArgs);

        if ($aResult !== false && $aResult->count() > 0) {
            $fChildAmount = (float) $aResult->fields['childTransactionsTotalAmount'];
        }

        // for the rounding precision use either the value the merchant set for currency
        // decimal precision or a fallback value
        $iRoundPrecision = Helper::getCurrencyRoundPrecision($oTransaction->wdoxidee_ordertransactions__currency);

        return round(bcsub($fBaseAmount, $fChildAmount, Helper::BCSUB_SCALE), $iRoundPrecision);
    }

    /**
     * Recursively calls itself until a transaction without a parent transaction ID is found.
     * This is the root transaction and its transaction ID is returned.
     *
     * @param string $sTransactionId
     *
     * @return string root transaction ID
     *
     * @since 1.1.0
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
     * Returns the needed post processing sdk transaction
     *
     * @param Transaction $oTransaction
     * @param string      $sActionTitle
     * @param array       $aOrderItems
     *
     * @return array|\Wirecard\PaymentSdk\Transaction\Transaction
     *
     * @since 1.1.0
     */
    private function _getPostProcessingTransaction($oTransaction, $sActionTitle, $aOrderItems)
    {
        try {
            $oPaymentMethod = PaymentMethodFactory::create($oTransaction->getPaymentType());
            return $oPaymentMethod->getPostProcessingTransaction($sActionTitle, $oTransaction, $aOrderItems);
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
     * @since 1.1.0
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
     * @since 1.1.0
     */
    private function _getErrorMessage($sMessage)
    {
        return ['status' => Transaction::STATE_ERROR, 'message' => $sMessage];
    }
}
