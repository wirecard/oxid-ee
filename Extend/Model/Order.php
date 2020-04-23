<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend\Model;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\OrderArticle;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;

use Psr\Log\LoggerInterface;

use Wirecard\Oxid\Core\AccountHolderHelper;
use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\PaymentMethodFactory;
use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\Oxid\Extend\Core\Email;
use Wirecard\Oxid\Model\Transaction;
use Wirecard\Oxid\Model\TransactionList;

use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Entity\AccountHolder;

/**
 * Class Order
 *
 * @mixin \OxidEsales\Eshop\Application\Model\Order
 *
 * @since 1.0.0
 */
class Order extends Order_parent
{
    // constant strings that are used by several response handlers to handle the specific merchant settings
    const STATE_CANCELLED = BackendService::TYPE_CANCELLED;
    const STATE_FAILED = 'failed';

    /**
     * @var LoggerInterface
     *
     * @since 1.0.0
     */
    private $_oLogger;

    /**
     * Order constructor.
     *
     * @since 1.1.0
     */
    public function __construct()
    {
        parent::__construct();
        $this->_oLogger = Registry::getLogger();
    }

    /**
     * Loads order data from DB.
     * Returns true on success.
     *
     * @param string $sTransactionId
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function loadWithTransactionId($sTransactionId)
    {
        //getting at least one field before lazy loading the object
        $this->_addField('wdoxidee_transactionid', 0);
        $sQuery = $this->buildSelectString([$this->getViewName() . '.wdoxidee_transactionid' => $sTransactionId]);
        $this->_isLoaded = $this->assignRecord($sQuery);

        return $this->_isLoaded;
    }

    /**
     * Returns the country associated with the order billing address.
     *
     * @return Country
     *
     * @since 1.0.0
     */
    public function getOrderBillingCountry()
    {
        $oCountry = oxNew(Country::class);
        $oCountry->load($this->oxorder__oxbillcountryid->value);

        return $oCountry;
    }

    /**
     * Returns the country associated with the order shipping address.
     *
     * @return Country
     *
     * @since 1.0.0
     */
    public function getOrderShippingCountry()
    {
        $oCountry = oxNew(Country::class);
        $oCountry->load($this->oxorder__oxdelcountryid->value);

        return $oCountry;
    }

    /**
     * Returns the payment associated with the order.
     *
     * @return Payment
     *
     * @since 1.0.0
     */
    public function getOrderPayment()
    {
        $sPaymentId = $this->oxorder__oxpaymenttype->value;
        return PaymentMethodHelper::getPaymentById($sPaymentId);
    }

    /**
     * Returns a TransactionList object containing all transactions associated with the order.
     *
     * @return TransactionList
     *
     * @since 1.0.0
     */
    public function getOrderTransactionList()
    {
        $oTransactionList = oxNew(TransactionList::class);

        return $oTransactionList->getListByConditions([
            'orderid' => $this->getId(),
        ]);
    }

    /**
     * Returns the last transaction associated with the order.
     *
     * @return Transaction
     *
     * @since 1.0.0
     */
    public function getOrderLastTransaction()
    {
        $oTransaction = oxNew(Transaction::class);
        $oTransaction->loadWithTransactionId($this->oxorder__wdoxidee_transactionid->value);

        return $oTransaction;
    }

    /**
     * Returns true if the payment is one of the module's.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function isCustomPaymentMethod()
    {
        return $this->getOrderPayment()->isCustomPaymentMethod();
    }

    /**
     * Checks if the payment is pending
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function isPaymentPending()
    {
        $oTransaction = $this->getOrderLastTransaction();

        return strpos($oTransaction->wdoxidee_ordertransactions__type->value, 'pending') !== false;
    }

    /**
     * Checks if the payment was successful
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function isPaymentSuccess()
    {
        return $this->oxorder__wdoxidee_orderstate->value === BackendService::TYPE_AUTHORIZED
            || $this->oxorder__wdoxidee_orderstate->value === BackendService::TYPE_PROCESSING;
    }

    /**
     * Checks if the payment failed
     *
     * @return bool
     *
     * @since 1.1.0
     */
    public function isPaymentFailed()
    {
        $oTransaction = $this->getOrderLastTransaction();

        return $oTransaction->wdoxidee_ordertransactions__type->value === self::STATE_FAILED;
    }

    /**
     * Checks if the payment was refunded
     *
     * @return bool
     *
     * @since 1.1.0
     */
    public function isPaymentRefunded()
    {
        return $this->oxorder__wdoxidee_orderstate->value === BackendService::TYPE_REFUNDED;
    }

    /**
     * Checks if the payment was cancelled
     *
     * @return bool
     *
     * @since 1.1.0
     */
    public function isPaymentCancelled()
    {
        return $this->oxorder__wdoxidee_orderstate->value === BackendService::TYPE_CANCELLED;
    }

    /**
     * Returns true if it is the last article in the order
     *
     * @param OrderArticle $oOrderItem
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function isLastArticle($oOrderItem)
    {
        $aArticles = $this->getOrderArticles(true)->getArray();
        foreach ($aArticles as $oArticle) {
            $oLastItem = $oArticle;
        }

        return $oLastItem->oxorderarticles__oxartid->value === $oOrderItem->oxorderarticles__oxartid->value;
    }

    /**
     * Returns an associative array of available states and their translation.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public static function getTranslatedStates()
    {
        return [
            BackendService::TYPE_PENDING => Helper::translate('wd_order_status_pending'),
            BackendService::TYPE_AUTHORIZED => Helper::translate('wd_order_status_authorized'),
            BackendService::TYPE_PROCESSING => Helper::translate('wd_order_status_purchased'),
            BackendService::TYPE_CANCELLED => Helper::translate('wd_order_status_cancelled'),
            BackendService::TYPE_REFUNDED => Helper::translate('wd_order_status_refunded'),
            self::STATE_FAILED => Helper::translate('wd_order_status_failed'),
        ];
    }

    /**
     * Creates an AccountHolder object for the order.
     *
     * @return AccountHolder
     *
     * @throws \OxidEsales\Eshop\Core\Exception\SystemComponentException
     *
     * @since 1.0.0
     */
    public function getAccountHolder()
    {
        $oCountry = $this->getOrderBillingCountry();
        $cCrmId = $this->getOrderUser()->hasAccount() ? $this->getOrderUser()->oxuser__oxcustnr->value : null;

        $aHiddenFields = PaymentMethodFactory::create($this->oxorder__oxpaymenttype->value)
            ->getHiddenAccountHolderFields();

        $aAccountHolderData = array_filter(
            [
                'countryCode' => $oCountry->oxcountry__oxisoalpha2->value,
                'city' => $this->oxorder__oxbillcity->value,
                'street' => $this->oxorder__oxbillstreet->value . ' ' . $this->oxorder__oxbillstreetnr->value,
                'state' => $this->oxorder__oxbillstateid->value,
                'postalCode' => $this->oxorder__oxbillzip->value,
                'firstName' => $this->oxorder__oxbillfname->value,
                'lastName' => $this->oxorder__oxbilllname->value,
                'phone' => $this->oxorder__oxbillfon->value,
                'email' => $this->oxorder__oxbillemail->value,
                'gender' => Helper::getGenderCodeForSalutation($this->oxorder__oxbillsal->value),
                'dateOfBirth' => Helper::getDateTimeFromString($this->getOrderUser()->oxuser__oxbirthdate->value),
                'crmId' => $cCrmId,
            ],
            function ($sKey) use ($aHiddenFields) {
                return !in_array($sKey, $aHiddenFields);
            },
            ARRAY_FILTER_USE_KEY
        );
        return AccountHolderHelper::createAccountHolder($aAccountHolderData);
    }

    /**
     * Creates a shipping AccountHolder object for the order.
     *
     * @return AccountHolder
     *
     * @since 1.0.0
     */
    public function getShippingAccountHolder()
    {
        // use shipping info if available
        $oCountry = $this->getOrderShippingCountry();
        if (!empty($oCountry->oxcountry__oxisoalpha2->value)) {
            return AccountHolderHelper::createAccountHolder([
                'countryCode' => $oCountry->oxcountry__oxisoalpha2->value,
                'city' => $this->oxorder__oxdelcity->value,
                'street' => $this->oxorder__oxdelstreet->value . ' ' . $this->oxorder__oxdelstreetnr->value,
                'state' => $this->oxorder__oxdelstateid->value,
                'postalCode' => $this->oxorder__oxdelzip->value,
                'firstName' => $this->oxorder__oxdelfname->value,
                'lastName' => $this->oxorder__oxdellname->value,
                'phone' => $this->oxorder__oxdelfon->value,
            ]);
        }

        // fallback to billing info
        $oCountry = $this->getOrderBillingCountry();
        return AccountHolderHelper::createAccountHolder([
            'countryCode' => $oCountry->oxcountry__oxisoalpha2->value,
            'city' => $this->oxorder__oxbillcity->value,
            'street' => $this->oxorder__oxbillstreet->value . ' ' . $this->oxorder__oxbillstreetnr->value,
            'state' => $this->oxorder__oxbillstateid->value,
            'postalCode' => $this->oxorder__oxbillzip->value,
            'firstName' => $this->oxorder__oxbillfname->value,
            'lastName' => $this->oxorder__oxbilllname->value,
            'phone' => $this->oxorder__oxbillfon->value,
        ]);
    }

    /**
     * Returns an array of available states.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public static function getStates()
    {
        return array_keys(self::getTranslatedStates());
    }

    /**
     * Returns the translation for the order's state.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getTranslatedState()
    {
        return self::getTranslatedStates()[$this->oxorder__wdoxidee_orderstate->value] ?? '';
    }

    /**
     *
     * Create a temporary Order that should not be saved but used for creating a
     * {@link Wirecard\PaymentSdk\Transaction\Transaction}
     *
     * @param Basket $oBasket
     * @param User   $oUser
     *
     * @since 1.0.0
     */
    public function createTemp($oBasket, $oUser)
    {
        $this->_setUser($oUser);
        $this->_loadFromBasket($oBasket);
        $this->oxorder__oxid = new Field(Registry::getSession()->getVariable('sess_challenge'));
    }

    /**
     * {@inheritdoc }
     * The emails will not be sent if module payment type is used
     *
     * @param object $oUser
     * @param object $oBasket
     * @param object $oPayment
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function _sendOrderByEmail($oUser = null, $oBasket = null, $oPayment = null)
    {
        if ($this->isCustomPaymentMethod()) {
            return self::ORDER_STATE_OK;
        }

        return parent::_sendOrderByEmail($oUser, $oBasket, $oPayment);
    }

    /**
     * Sends order by email
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function sendOrderByEmail()
    {
        $this->getOrderBasket();
        $this->getOrderUserPayment();
        $this->getOrderUser();

        return $this->_tryToSendOrderByEmail();
    }

    /**
     * Returns the basket of the order and also sets it into private attribute
     *
     * @return \OxidEsales\Eshop\Application\Model\Basket
     *
     * @since 1.0.0
     */
    public function getOrderBasket()
    {
        if (empty($this->_oBasket)) {
            $this->_oBasket = $this->_getOrderBasket(false);
            $this->_addOrderArticlesToBasket($this->_oBasket, $this->getOrderArticles(true));
            $this->_oBasket->calculateBasket(true);
        }

        return $this->_oBasket;
    }

    /**
     * Returns the user payment of the order and also sets it into private attribute
     *
     * @return \OxidEsales\Eshop\Application\Model\UserPayment
     *
     * @since 1.0.0
     */
    public function getOrderUserPayment()
    {
        if (empty($this->_oPayment)) {
            $this->_oPayment = $this->_setPayment($this->_oBasket->getPaymentId());
        }

        return $this->_oPayment;
    }

    /**
     * Send order by emails. Respect settings for sending pending emails
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function _tryToSendOrderByEmail()
    {
        $bRet = false;

        $oEmail = oxNew(Email::class);
        $bSendPendingEmails = $this->getConfig()->getConfigParam('wd_email_on_pending_orders');

        // Dont send pending emails if not enabled in module settings
        if ($this->isPaymentPending() && !$bSendPendingEmails) {
            $this->_oLogger->debug('Prevent sending pending order by email with id: ' . $this->getId());
            return true;
        }

        if ($oEmail->sendOrderEmailToUser($this)) {
            $bRet = true;
        }

        $oEmail->sendOrderEmailToOwner($this);

        return $bRet;
    }

    /**
     * Handles the order after a certain order state is set.
     *
     * @param string $sState
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function handleOrderState($sState)
    {
        if ($this->_shouldBeDeletedOnState($sState)) {
            if ($this->delete()) {
                $this->_oLogger->info(
                    "Order `{$this->getId()}` was deleted as requested by the payment method config."
                );

                return;
            }

            $this->_oLogger->error(
                "Order `{$this->getId()}` could not be deleted as requested by the payment method config."
            );
        }
        // Change order state if consumer cancelled the order, or if there was a payment error.
        // This is done whenever consumer gets redirected back to a cancel or error redirect url.
        $this->oxorder__wdoxidee_orderstate = new Field($sState);
        $this->save();
    }

    /**
     * Checks if the order should be deleted if a certain order state is set.
     *
     * @param string $sState
     *
     * @return bool
     *
     * @since 1.0.0
     */
    private function _shouldBeDeletedOnState($sState)
    {
        $oPayment = $this->getOrderPayment();

        return ($sState === self::STATE_CANCELLED && $oPayment->oxpayments__wdoxidee_delete_canceled_order->value) ||
            ($sState === self::STATE_FAILED && $oPayment->oxpayments__wdoxidee_delete_failed_order->value);
    }
}
