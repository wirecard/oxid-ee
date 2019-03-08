<?php
/**
 *
 *  Shop System Plugins - Terms of Use
 *
 *  The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 *  of the Wirecard AG range of products and services.
 *
 *  They have been tested and approved for full functionality in the standard configuration
 *  (status on delivery) of the corresponding shop system. They are under General Public
 *  License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 *  the same terms.
 *
 *  However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 *  occurring when used in an enhanced, customized shop system configuration.
 *
 *  Operation in an enhanced, customized configuration is at your own risk and requires a
 *  comprehensive test phase by the user of the plugin.
 *
 *  Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 *  functionality neither does Wirecard AG assume liability for any disadvantages related to
 *  the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 *  for customized shop systems or installed plugins of other vendors of plugins within the same
 *  shop system.
 *
 *  Customers are responsible for testing the plugin's functionality before starting productive
 *  operation.
 *
 *  By installing the plugin into the shop system the customer agrees to these terms of use.
 *  Please do not use the plugin if you do not agree to these terms of use!
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
            return ['status' => 'error', 'message' => $this->oLang->translateString('error_tranaction_cancel')];
        }
    }

    private function _restockItems(Order $oOrder)
    {
        $oOrder->cancelOrder();
    }
}
