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
use OxidEsales\Eshop\Core\Registry;

use OxidEsales\EshopCommunity\Core\Field;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\TransactionService;

class Transaction_Handler
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $oLogger;

    /**
     * @var \OxidEsales\EshopCommunity\Core\Language
     */
    private $oLang;

    public function __construct()
    {
        $this->oLogger = Registry::getLogger();
        $this->oLang = Registry::getLang();
    }

    /**
     * @param \Wirecard\Oxid\Model\Transaction $oTransaction
     * @return array either [status => success] or [status => error, message => errorMessage]
     */
    public function processCancel(\Wirecard\Oxid\Model\Transaction $oTransaction): array
    {
        try {
            $oPayment = Payment_Method_Factory::create($oTransaction->getPaymentType());
        } catch (\Exception $oExc) {
            $this->oLogger->error("Error canceling transaction", [$oExc]);
            return ['status' => 'error', 'message' => $oExc->getMessage()];
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

        $oTransactionService = new TransactionService($oConfig, $this->oLogger);
        try {
            /**
             * @var $oResponse \Wirecard\PaymentSdk\Response\Response
             */
            $oResponse = $oTransactionService->cancel($oCancelTransaction);
        } catch (\Exception $oExc) {
            $this->oLogger->error("Error canceling transaction", [$oExc]);
        }

        if ($oResponse instanceof SuccessResponse) {
            $this->_restockItems($oOrder);
            $oTransaction->wdoxidee_ordertransactions__wdoxidee_transactionstatus = new Field('cancelled');
            $oTransaction->save();
            return ['status' => 'success'];
        }

        if ($oResponse instanceof FailureResponse) {
            return ['status' => 'error', 'message' => $this->oLang->translateString('error_transaction_cancel')];
        }
    }

    private function _restockItems(Order $oOrder)
    {
        $oOrder->cancelOrder();
    }
}
