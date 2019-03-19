<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use Wirecard\Oxid\Core\Helper;

use OxidEsales\Eshop\Core\Model\MultiLanguageModel;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Payment;

/**
 * Transaction
 *
 */
class Transaction extends MultiLanguageModel
{
    const ACTION_AUTHORIZE_CAPTURE = 'authorize-capture';
    const ACTION_PURCHASE = 'purchase';

    const STATE_AWAITING = 'awaiting';
    const STATE_SUCCESS = 'success';
    const STATE_CLOSED = 'closed';
    const STATE_ERROR = 'error';

    /**
     * @inheritdoc
     */
    protected $_sClassName = 'transaction';

    /**
     * Transaction constructor
     */
    public function __construct()
    {
        parent::__construct();
        // allow Oxid's magic getters for database table
        $this->init('wdoxidee_ordertransactions');
    }

    /**
     * Returns the order associated with the transaction.
     *
     * @return Order
     */
    public function getOrder(): Order
    {
        $oOrder = oxNew(Order::class);
        $oOrder->load($this->wdoxidee_ordertransactions__orderid->value);

        return $oOrder;
    }

    /**
     * Returns the payment associated with the transaction's order.
     *
     * @return Payment
     */
    public function getPayment(): Payment
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->load($this->getOrder()->oxorder__oxpaymenttype->value);

        return $oPayment;
    }

    /**
     * Returns the decoded response XML.
     *
     * @return string
     */
    public function getResponseXML(): string
    {
        return base64_decode($this->wdoxidee_ordertransactions__responsexml->rawValue);
    }

    /**
     * Loads transaction data from DB.
     * Returns true on success.
     *
     * @param string $sTransactionId
     *
     * @return bool
     */
    public function loadWithTransactionId(string $sTransactionId)
    {
        //getting at least one field before lazy loading the object
        $this->_addField('transactionid', 0);
        $query = $this->buildSelectString([$this->getViewName() . '.transactionid' => $sTransactionId]);
        $this->_isLoaded = $this->assignRecord($query);

        return $this->_isLoaded;
    }

    /**
     * Returns an associative array of available actions and their translation.
     *
     * @return array
     */
    public static function getTranslatedActions(): array
    {
        return [
            self::ACTION_AUTHORIZE_CAPTURE => Helper::translate('text_payment_action_reserve'),
            self::ACTION_PURCHASE => Helper::translate('text_payment_action_pay'),
        ];
    }

    /**
     * Returns an array of available actions.
     *
     * @return array
     */
    public static function getActions(): array
    {
        return array_keys(self::getTranslatedActions());
    }

    /**
     * Returns an array of available states.
     *
     * @return array
     */
    public static function getStates(): array
    {
        return [
            self::STATE_SUCCESS,
            self::STATE_ERROR,
            self::STATE_AWAITING,
            self::STATE_CLOSED
        ];
    }
}
