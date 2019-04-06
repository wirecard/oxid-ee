<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller;

use \Wirecard\Oxid\Core\Payment_Method_Factory;
use \Wirecard\Oxid\Model\Transaction;

use \Wirecard\PaymentSdk\BackendService;
use \Wirecard\PaymentSdk\Response\Response;
use \Wirecard\PaymentSdk\Exception\MalformedResponseException;
use \Wirecard\PaymentSdk\Response\SuccessResponse;

use \OxidEsales\Eshop\Application\Model\Order as Oxid_Order;
use \OxidEsales\Eshop\Application\Controller\FrontendController;
use \OxidEsales\Eshop\Application\Model\Payment;
use \OxidEsales\Eshop\Core\Registry;
use \OxidEsales\Eshop\Core\Field;

use \Psr\Log\LoggerInterface;
use \InvalidArgumentException;
use \Exception;

/**
 * Notify handler class.
 *
 * Handle Payment SDK notifications.
 */
class NotifyHandler extends FrontendController
{
    /**
     * @var LoggerInterface
     */
    private $_oLogger;

    /**
     * NotifyHandler constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->_oLogger = Registry::getLogger();
    }

    /**
     * Request handling function.
     *
     * @return void
     * @throws Exception if $sPaymentName does not exist
     */
    public function handleRequest()
    {
        $sPaymentName = Registry::getRequest()->getRequestParameter('pmt');
        $oPaymentMethod = Payment_Method_Factory::create($sPaymentName);
        $oConfig = $oPaymentMethod->getConfig();
        $sPostData = file_get_contents('php://input');

        try {
            $oService = new BackendService($oConfig, $this->_oLogger);
            $oNotificationResponse = $oService->handleNotification($sPostData);
        } catch (InvalidArgumentException $exception) {
            $this->_oLogger->error(__METHOD__ . ': Invalid argument set: '. $exception->getMessage(), [$exception]);
            return;
        } catch (MalformedResponseException $exception) {
            $this->_oLogger->error(__METHOD__ . ': Response is malformed: '. $exception->getMessage(), [$exception]);
            return;
        }

        // Return the response or log errors if any happen.
        if ($oNotificationResponse instanceof SuccessResponse && $oNotificationResponse->isValidSignature()) {
            $this->_onNotificationSuccess($oNotificationResponse, $oService);
        } else {
            $this->_onNotificationError($oNotificationResponse);
        }
    }

    /**
     * Handles success notifications
     *
     * @param SuccessResponse $oResponse
     * @param BackendService  $oBackendService
     *
     * @return void
     */
    private function _onNotificationSuccess(Response $oResponse, BackendService $oBackendService)
    {
        $this->_oLogger->debug('Notification response: ' . $oResponse->getRawData());
        $aData = $oResponse->getData();
        $oUtilsDate = Registry::getUtilsDate();
        $sConvertedTimestamp = $oUtilsDate->formatDBTimestamp($oUtilsDate->formTime($aData['completion-time-stamp']));

        $oOrder = oxNew(Oxid_Order::class);
        if (!$oOrder->loadWithTransactionId($oResponse->getParentTransactionId())) {
            $this->_oLogger->error('No order found for transactionId: ' . $oResponse->getParentTransactionId());
            return;
        }

        $sPaymentMethod = $oOrder->oxorder__oxpaymenttype->value;
        $oPayment = oxNew(Payment::class);
        $oPayment->load($sPaymentMethod);

        $oTransaction = oxNew(Transaction::class);
        $oTransaction->wdoxidee_ordertransactions__ordernumber = new Field($oOrder->oxorder__oxordernr->value);
        $oTransaction->wdoxidee_ordertransactions__orderid = new Field($oOrder->oxorder__oxid->value);
        $oTransaction->wdoxidee_ordertransactions__transactionid = new Field($oResponse->getTransactionId());
        $oTransaction->wdoxidee_ordertransactions__parenttransactionid
            = new Field($this->_getParentTransactionId($oResponse));
        $oTransaction->wdoxidee_ordertransactions__requestid = new Field($oResponse->getRequestId());
        $oTransaction->wdoxidee_ordertransactions__action
            = new Field($oPayment->oxpayments__wdoxidee_transactionaction->value);
        $oTransaction->wdoxidee_ordertransactions__type = new Field($oResponse->getTransactionType());
        $oTransaction->wdoxidee_ordertransactions__state = new Field($aData['transaction-state']);
        $oTransaction->wdoxidee_ordertransactions__amount = new Field($oResponse->getRequestedAmount()->getValue());
        $oTransaction->wdoxidee_ordertransactions__currency
            = new Field($oResponse->getRequestedAmount()->getCurrency());
        $oTransaction->wdoxidee_ordertransactions__responsexml = new Field(base64_encode($oResponse->getRawData()));
        $oTransaction->wdoxidee_ordertransactions__date = new Field($sConvertedTimestamp);
        $oTransaction->save();

        $oOrder->oxorder__wdoxidee_providertransactionid = new Field($aData['statuses.0.provider-transaction-id']);
        $oOrder->oxorder__wdoxidee_transactionid = new Field($oResponse->getTransactionId());
        $oOrder->oxorder__oxpaid = new Field($sConvertedTimestamp);
        $oOrder->save();

        if ($oOrder->oxorder__wdoxidee_final->value) {
            $this->_oLogger->warning('Corresponding order is already finished, nothing updated!');
            return;
        }

        $oOrder->oxorder__wdoxidee_orderstate
            = new Field($oBackendService->getOrderState($oResponse->getTransactionType()));
        $oOrder->oxorder__wdoxidee_final = new Field(1);
        $oOrder->save();
    }

    /**
     * Handles error notifications
     *
     * @param Response $oResponse
     */
    private function _onNotificationError(Response $oResponse)
    {
        $this->_oLogger->error(__METHOD__ . ': Error processing transaction:');

        foreach ($oResponse->getStatusCollection()->getIterator() as $oItem) {
            $this->_oLogger->error("\t Status with code ". $oItem->getCode() ." and message ". $oItem->getDescription());
        }
    }

    /**
     * Get parent transaction id, returns null if there is no parent transaction in the table
     *
     * @param Response $oResponse
     * @return string
     */
    private function _getParentTransactionId(Response $oResponse)
    {
        $sParentTransactionId = $oResponse->getParentTransactionId();
        $oTransaction = oxNew(Transaction::class);
        if (!$oTransaction->loadWithTransactionId($sParentTransactionId)) {
            return null;
        }

        return $sParentTransactionId;
    }
}
