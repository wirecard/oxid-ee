<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Model\MultiLanguageModel;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\OxidEeEvents;
use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\Oxid\Core\ResponseMapper;

use Wirecard\PaymentSdk\Entity\Basket;

/**
 * Transaction
 *
 * @since 1.0.0
 */
class Transaction extends MultiLanguageModel
{
    const ACTION_RESERVE = 'reserve';
    const ACTION_PAY = 'pay';
    const ACTION_CREDIT = 'credit';

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

    /**
     * @var array
     *
     * @since 1.0.0
     */
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
     * @see   \OxidEsales\EshopCommunity\Core\Model\BaseModel::load
     *
     * @param string $sTransactionId
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function loadWithTransactionId($sTransactionId)
    {
        $this->_addField('transactionid', 0);
        $sQuery = $this->buildSelectString([$this->getViewName() . '.transactionid' => $sTransactionId]);
        $this->_isLoaded = $this->assignRecord($sQuery);

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
     * Returns true if transaction's payment method is Payment on Invoice or Payment in Advance
     *
     * @return bool
     *
     * @since 1.3.0
     */
    public function isPoiPiaPaymentMethod()
    {
        $sPaymentId = $this->getPaymentType();
        $oPayment = PaymentMethodHelper::getPaymentById($sPaymentId);

        return is_subclass_of($oPayment->getPaymentMethod(), BasePoiPiaPaymentMethod::class);
    }

    /**
     * Returns the order associated with the transaction.
     *
     * @return Order
     *
     * @since 1.0.0
     */
    public function getTransactionOrder()
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
    public function getResponseXML()
    {
        return base64_decode($this->wdoxidee_ordertransactions__responsexml->rawValue);
    }

    /**
     * Creates a transaction and saves it in the database.
     *
     * @param array $aArgs associative array containing the fields to be set on the transaction object
     *
     * @return bool indicating whether the creation of the database entry was successful
     *
     * @since 1.1.0
     */
    public static function createDbEntryFromArray($aArgs)
    {
        $sColumnNames = '`' . implode('`,`', array_keys($aArgs)) . '`';

        // quote the values to be safe
        $fQuoteString = function ($sValue) {
            return DatabaseProvider::getDb()->quote($sValue);
        };

        $aArgValues = array_map($fQuoteString, array_values($aArgs));
        $sValues = implode(',', array_values($aArgValues));

        $sQuery = "INSERT INTO " . OxidEeEvents::TRANSACTION_TABLE . "
                        (" . $sColumnNames . ")
                    VALUES
                        (" . $sValues . ")
                    ON DUPLICATE KEY
                        UPDATE transactionid = transactionid";

        $iResult = DatabaseProvider::getDb()->execute($sQuery);

        return $iResult > 0;
    }

    /**
     * Returns an associative array of selectable actions and their translation.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public static function getTranslatedActions()
    {
        return [
            self::ACTION_RESERVE => Helper::translate('wd_text_payment_action_reserve'),
            self::ACTION_PAY => Helper::translate('wd_text_payment_action_pay'),
        ];
    }

    /**
     * Returns an array of available actions.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public static function getActions()
    {
        return [
            self::ACTION_RESERVE,
            self::ACTION_PAY,
            self::ACTION_CREDIT,
        ];
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
        return [
            self::STATE_SUCCESS,
            self::STATE_ERROR,
            self::STATE_AWAITING,
            self::STATE_CLOSED,
        ];
    }

    /**
     * Returns an array of translated states.
     *
     * @return array
     *
     * @since 1.3.0
     */
    public static function getTranslatedStates()
    {
        return [
            self::STATE_SUCCESS => Helper::translate('wd_state_success'),
            self::STATE_ERROR => Helper::translate('wd_state_error'),
            self::STATE_AWAITING => Helper::translate('wd_state_awaiting'),
            self::STATE_CLOSED => Helper::translate('wd_state_closed'),
        ];
    }

    /**
     * Returns the translation for the transaction's state.
     *
     * @return string
     *
     * @since 1.3.0
     */
    public function getTranslatedState()
    {
        return self::getTranslatedStates()[$this->wdoxidee_ordertransactions__state->value] ?? '';
    }

    /**
     * @return string
     *
     * @since 1.1.0
     */
    public function getPaymentType()
    {
        return $this->getTransactionOrder()->oxorder__oxpaymenttype->value;
    }

    /**
     * Returns the Basket object
     *
     * @return Basket
     *
     * @since 1.2.0
     */
    public function getBasket()
    {
        return (new ResponseMapper($this->getResponseXML()))->getResponse()->getBasket();
    }
}
