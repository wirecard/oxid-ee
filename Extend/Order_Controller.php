<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend;

use \OxidEsales\Eshop\Application\Model\Basket;
use \Wirecard\Oxid\Model\Credit_Card_Payment_Method;
use \Wirecard\PaymentSdk\Config\Config;
use \Wirecard\PaymentSdk\Entity\Amount;
use \Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
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

    /**
     * @var Credit_Card_Payment_Method
     */
    private $_oCreditCardPaymentMethod;

    /**
     * @var Config
     */
    private $_oPaymentMethodConfig;

    public function getCreditCardUiWithData()
    {
        $oTransactionService = $this->_getTransactionService();
        $oTransaction = $this->_getCreditCardPaymentMethod()->getTransaction();

        /**
         * @var $oBasket Basket
         */
        $oBasket = $this->getBasket();

        $oTransaction->setAmount(new Amount($oBasket->getPrice()->getBruttoPrice(), $oBasket->getBasketCurrency()->name));
        $oTransaction->setConfig($this->_getConfig()->get(CreditCardTransaction::NAME));

        //TODO set correct payment action
        //TODO set correct language
        return $oTransactionService->getCreditCardUiWithData($oTransaction, 'authorize');
    }

    /**
     * @return TransactionService
     */
    private function _getTransactionService()
    {
        if (is_null($this->_oTransactionService)) {
            $this->_oTransactionService = new TransactionService($this->_getConfig());
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

    private function _getConfig()
    {
        if (is_null($this->_oPaymentMethodConfig)) {
            $oPayment = $this->getPayment();
            $this->_oPaymentMethodConfig = $this->_getCreditCardPaymentMethod()->getConfig($oPayment);
        }

        return $this->_oPaymentMethodConfig;

    }
}
