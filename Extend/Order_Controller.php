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
use OxidEsales\EshopCommunity\Core\Registry;
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

    /**
     *
     * Returns the parameters used to render the credit card form
     *
     * @return string
     */
    public function getCreditCardUiWithData(): string
    {
        $oTransactionService = $this->_getTransactionService();
        /**
         * @var $oTransaction CreditCardTransaction
         */
        $oTransaction = $this->_getCreditCardPaymentMethod()->getTransaction();

        /**
         * @var $oBasket Basket
         */
        $oBasket = $this->getBasket();

        //TODO
        //$oTransaction->setAmount(new Amount(
        //$oBasket->getPrice()->getBruttoPrice(),
        // $oBasket->getBasketCurrency()->name)
        //);
        $oTransaction->setAmount(new Amount(0, "EUR"));
        $oTransaction->setConfig($this->_getPaymentMethodConfig()->get(CreditCardTransaction::NAME));

        //TODO correct setTermUrl
        $oTransaction->setTermUrl($this->getConfig()->getCurrentShopUrl() . "/termUrl.php");
        return $oTransactionService->getCreditCardUiWithData(
            $oTransaction,
            $this->_getPaymentAction("pay"),
            Registry::getLang()->getLanguageAbbr()
        );
    }

    /**
     * @return TransactionService
     */
    private function _getTransactionService(): TransactionService
    {
        if (is_null($this->_oTransactionService)) {
            $this->_oTransactionService = new TransactionService($this->_getPaymentMethodConfig());
        }

        return $this->_oTransactionService;
    }

    /**
     * @return Credit_Card_Payment_Method
     */
    private function _getCreditCardPaymentMethod(): Credit_Card_Payment_Method
    {
        if (is_null($this->_oCreditCardPaymentMethod)) {
            $this->_oCreditCardPaymentMethod = new Credit_Card_Payment_Method();
        }

        return $this->_oCreditCardPaymentMethod;
    }

    /**
     * @return Config
     */
    private function _getPaymentMethodConfig(): Config
    {
        if (is_null($this->_oPaymentMethodConfig)) {
            $oPayment = $this->getPayment();
            $this->_oPaymentMethodConfig = $this->_getCreditCardPaymentMethod()->getConfig($oPayment);
        }

        return $this->_oPaymentMethodConfig;
    }

    /**
     * Converts the admin panel payment method action into a seamless
     * @param string $sAction
     * @return string
     */
    private function _getPaymentAction(string $sAction): string
    {
        if ($sAction == 'pay') {
            return 'purchase';
        }
        return 'authorization';
    }
}
