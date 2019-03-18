<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use \OxidEsales\Eshop\Application\Model\Order;
use \OxidEsales\Eshop\Core\Registry;
use \OxidEsales\EshopCommunity\Core\Field;

use \Wirecard\PaymentSdk\Entity\Amount;
use \Wirecard\PaymentSdk\Response\FailureResponse;
use \Wirecard\PaymentSdk\Response\SuccessResponse;
use \Wirecard\PaymentSdk\TransactionService;

use \Wirecard\Oxid\Model\Transaction;

class Transaction_Handler
{
    const TRANSACTION_STATUS_SUCCESS = 'success';
    const TRANSACTION_STATUS_ERROR = 'error';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_oLogger;

    /**
     * @var \OxidEsales\EshopCommunity\Core\Language
     */
    private $_oLang;

    public function __construct()
    {
        $this->_oLogger = Registry::getLogger();
        $this->_oLang = Registry::getLang();
    }

    /**
     * @param Transaction $oTransaction
     * @return array either [status => success] or [status => error, message => errorMessage]
     */
    public function processCancel(Transaction $oTransaction): array
    {
        try {
            $oPayment = Payment_Method_Factory::create($oTransaction->getPaymentType());
        } catch (\Exception $oExc) {
            $this->_oLogger->error("Error canceling transaction", [$oExc]);
            return ['status' => self::TRANSACTION_STATUS_ERROR, 'message' => $oExc->getMessage()];
        }

        $oConfig = $oPayment->getConfig();
        $oOrder = $oTransaction->getOrder();

        /**
         * @var $oTransaction \Wirecard\PaymentSdk\Transaction\Transaction
         */
        $oCancelTransaction = $oPayment->getCancelTransaction();
        $oCancelTransaction->setParentTransactionId($oTransaction->getId());
        $oCancelTransaction->setAmount(new Amount(
            $oTransaction->wdoxidee_ordertransactions__wdoxidee_amount->value,
            $oOrder->oxorder__oxcurrency->name
        ));

        $oTransactionService = new TransactionService($oConfig, $this->_oLogger);
        try {
            /**
             * @var $oResponse \Wirecard\PaymentSdk\Response\Response
             */
            $oResponse = $oTransactionService->cancel($oCancelTransaction);
        } catch (\Exception $oExc) {
            $this->_oLogger->error("Error canceling transaction", [$oExc]);
        }

        if ($oResponse instanceof SuccessResponse) {
            $this->_restockItems($oOrder);
            $oTransaction->wdoxidee_ordertransactions__wdoxidee_transactionstatus = new Field('cancelled');
            $oTransaction->save();
            return ['status' => self::TRANSACTION_STATUS_SUCCESS];
        }

        if ($oResponse instanceof FailureResponse) {
            return ['status' => self::TRANSACTION_STATUS_ERROR, 'message' => $this->_oLang->translateString('error_transaction_cancel')];
        }
    }

    private function _restockItems(Order $oOrder)
    {
        $oOrder->cancelOrder();
    }
}
