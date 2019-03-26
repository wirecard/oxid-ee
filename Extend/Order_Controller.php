<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend;

use \Wirecard\Oxid\Model\Credit_Card_Payment_Method;
use \Wirecard\PaymentSdk\TransactionService;

/**
 * Class Order
 *
 * @package Wirecard\Extend
 *
 * @mixin \OxidEsales\Eshop\Application\Model\Order
 */
class Order_Controller extends Order_Controller_parent
{
    /**
     * @var TransactionService
     */
    private $_oTransactionService;

    private $_oCreditCardPaymentMethod;

    public function getCreditCardUiWithData()
    {
        $oTransactionService = $this->_getTransactionService();
        $oTransaction = $this->_getCreditCardPaymentMethod()->getTransaction();
        //TODO cgrach set order amount
        //$oTransaction->setAmount(new Amount(1.0, "EUR"));

        //TODO set correct payment action
        return $oTransactionService->getCreditCardUiWithData($oTransaction, 'authorize');
    }

    /**
     * @return TransactionService
     */
    private function _getTransactionService()
    {
        if (is_null($this->_oTransactionService)) {
            $oPayment = $this->getPayment();
            $this->_oTransactionService = new TransactionService($this->_getCreditCardPaymentMethod()->getConfig($oPayment));
        }

        return $this->_oTransactionService;
    }

    /**
     * @return Credit_Card_Payment_Method
     */
    private function _getCreditCardPaymentMethod()
    {
        if (is_null($this->_oCreditCardPaymentMethod)) {
            $this->_oCreditCardPaymentMethod = new Credit_Card_Payment_Method();
        }

        return $this->_oCreditCardPaymentMethod;
    }
}
