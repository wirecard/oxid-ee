<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;

use Psr\Log\LoggerInterface;

use Wirecard\Oxid\Extend\Model\Payment;
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

        $sPaymentMethod = $oOrder->oxorder__oxpaymenttype->value;
        $oPayment = oxNew(Payment::class);
        $oPayment->load($sPaymentMethod);

        self::_saveTransaction($oResponse, $oOrder, $oPayment);
        self::_updateOrder($oOrder, $oResponse, $oBackendService);
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
    private static function _getParentTransactionId(Response $oResponse)
    {
        $sParentTransactionId = $oResponse->getParentTransactionId();
        $oTransaction = oxNew(Transaction::class);
        if (!$oTransaction->loadWithTransactionId($sParentTransactionId)) {
            return null;
        }

        return $sParentTransactionId;
    }

    /**
     * @param SuccessResponse $oResponse
     * @param Order           $oOrder
     * @param Payment         $oPayment
     *
     * @throws \Exception
     *
     * @since 1.0.0
     */
    private static function _saveTransaction($oResponse, $oOrder, $oPayment)
    {
        $aData = $oResponse->getData();

        $oTransaction = oxNew(Transaction::class);
        $oTransaction->wdoxidee_ordertransactions__ordernumber = new Field($oOrder->oxorder__oxordernr->value);
        $oTransaction->wdoxidee_ordertransactions__orderid = new Field($oOrder->oxorder__oxid->value);
        $oTransaction->wdoxidee_ordertransactions__transactionid = new Field($oResponse->getTransactionId());
        $oTransaction->wdoxidee_ordertransactions__parenttransactionid
            = new Field(self::_getParentTransactionId($oResponse));
        $oTransaction->wdoxidee_ordertransactions__requestid = new Field($oResponse->getRequestId());
        $oTransaction->wdoxidee_ordertransactions__action
            = new Field($oPayment->oxpayments__wdoxidee_transactionaction->value);
        $oTransaction->wdoxidee_ordertransactions__type = new Field($oResponse->getTransactionType());
        $oTransaction->wdoxidee_ordertransactions__state = new Field($aData['transaction-state']);

        $oTransaction->wdoxidee_ordertransactions__amount = new Field($aData['requested-amount']);
        $oTransaction->wdoxidee_ordertransactions__currency = new Field($aData['currency']);

        $oTransaction->wdoxidee_ordertransactions__responsexml = new Field(base64_encode($oResponse->getRawData()));
        $oTransaction->wdoxidee_ordertransactions__date = new Field(
            Helper::getFormattedDbDate($oResponse->getData()['completion-time-stamp'])
        );
        $oTransaction->save();
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
