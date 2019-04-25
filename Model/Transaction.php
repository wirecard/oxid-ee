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
use Wirecard\Oxid\Model\TransactionList;

use OxidEsales\Eshop\Core\Model\MultiLanguageModel;
use OxidEsales\Eshop\Application\Model\Order;

/**
 * Transaction
 *
 * @since 1.0.0
 */
class Transaction extends MultiLanguageModel
{
    const ACTION_RESERVE = 'reserve';
    const ACTION_PAY = 'pay';

    const STATE_AWAITING = 'awaiting';
    const STATE_SUCCESS = 'success';
    const STATE_CLOSED = 'closed';
    const STATE_ERROR = 'error';

    /**
     * @inheritdoc
     *
     * @since 1.0.0
     */
    protected $_sClassName = 'transaction';

    protected $_aChildTransactions = [];

    /**
     * @inheritdoc
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct();
        $this->init('wdoxidee_ordertransactions');
    }

    /**
     * @inheritdoc
     *
     * @param array $aRecord
     *
     * @since 1.0.0
     */
    public function assign($aRecord)
    {
        parent::assign($aRecord);

        // set child transactions
        $oTransactionList = oxNew(TransactionList::class);

        $this->_aChildTransactions = $oTransactionList->getListByConditions([
            'parenttransactionid' => $this->wdoxidee_ordertransactions__transactionid->value,
        ])->getArray();
    }

    /**
     * Loads a Transaction by transaction ID.
     *
     * @see \OxidEsales\EshopCommunity\Core\Model\BaseModel::load
     * @param string $sTransactionId
     * @return bool
     *
     * @since 1.0.0
     */
    public function loadWithTransactionId(string $sTransactionId)
    {
        $this->_addField('transactionid', 0);
        $query = $this->buildSelectString([$this->getViewName() . '.transactionid' => $sTransactionId]);
        $this->_isLoaded = $this->assignRecord($query);

        return $this->isLoaded();
    }

    /**
     * Returns an array of child transactions for this transaction.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getChildTransactions()
    {
        return $this->_aChildTransactions;
    }

    /**
     * Returns the order associated with the transaction.
     *
     * @return Order
     *
     * @since 1.0.0
     */
    public function getTransactionOrder(): Order
    {
        $oOrder = oxNew(Order::class);
        $oOrder->load($this->wdoxidee_ordertransactions__orderid->value);

        return $oOrder;
    }

    /**
     * Returns the decoded response XML.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getResponseXML(): string
    {
        return base64_decode($this->wdoxidee_ordertransactions__responsexml->rawValue);
    }

    /**
     * Creates a transaction and saves it in the database.
     *
     * @param array $aArgs associative array containing the fields to be set on the transaction object
     *
     * @return string|bool indicating whether the creation of the database entry was successful
     */
    public static function createDbEntryFromArray(array $aArgs)
    {
        $oTransaction = oxNew(Transaction::class);
        $oTransaction->assign($aArgs);
        return $oTransaction->save();
    }

    /**
     * Returns an associative array of available actions and their translation.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public static function getTranslatedActions(): array
    {
        return [
            self::ACTION_RESERVE => Helper::translate('text_payment_action_reserve'),
            self::ACTION_PAY => Helper::translate('text_payment_action_pay'),
        ];
    }

    /**
     * Returns an array of available actions.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public static function getActions(): array
    {
        return array_keys(self::getTranslatedActions());
    }

    /**
     * Returns an array of available states.
     *
     * @return array
     *
     * @since 1.0.0
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

    /**
     * @return string
     */
    public function getPaymentType(): string
    {
        return $this->getTransactionOrder()->oxorder__oxpaymenttype->value;
    }
}
