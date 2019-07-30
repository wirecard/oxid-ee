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
use Wirecard\Oxid\Model\PaymentMethod\BasePoiPiaPaymentMethod;

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
     * Returns true if transaction's xml payment method is "wiretransfer"
     *
     * @return bool
     *
     * @since 1.3.0
     */
    public function isPoiPiaPaymentMethod()
    {
        return self::_getTransactionXmlPaymentMethod() === BasePoiPiaPaymentMethod::PAYMENT_METHOD_WIRETRANSFER;
    }

    /**
     * Returns payment method from transaction xml object
     *
     * @return string
     *
     * @since 1.3.0
     */
    private function _getTransactionXmlPaymentMethod()
    {
        $oXml = simplexml_load_string($this->getResponseXML());
        return (string) $oXml->{'payment-methods'}->{'payment-method'}['name'];
    }

    /**
     * Returns payment method name by transaction order reference if there is order reference.
     *
     * @return string|void
     *
     * @since 1.3.0
     */
    private function _getPaymentMethodNameFromTransactionOrderReference()
    {
        $sPaymentId = $this->getPaymentType();
        if ($sPaymentId) { // there is an order associated with the transaction
            $oPayment = PaymentMethodHelper::getPaymentById($sPaymentId);
            return $oPayment->oxpayments__oxdesc->value;
        }
    }

    /**
     * Gets payment method from transaction's xml and tries to find corresponding payment method
     * name in database.
     * If it does not exist, returns payment method from transaction's xml as a fallback.
     *
     * @return string
     *
     * @since 1.3.0
     */
    private function _getPaymentMethodNameFromTransactionXmlPaymentMethod()
    {
        $sPaymentId = self::_getTransactionXmlPaymentMethod();
        $oPayment = PaymentMethodHelper::getPaymentById('wd' . $sPaymentId);

        if (!$oPayment->oxpayments__oxid->value) {
            return $sPaymentId;
        }
        return $oPayment->oxpayments__oxdesc->value;
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
     * Returns transaction's payment method name.
     *
     * @return string
     *
     * @since 1.3.0
     */
    public function getTransactionPaymentMethodName()
    {
        $sPaymentMethodName = self::_getPaymentMethodNameFromTransactionOrderReference();
        if ($sPaymentMethodName) {
            return $sPaymentMethodName;
        }

        return self::_getPaymentMethodNameFromTransactionXmlPaymentMethod();
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
     * Returns payment type from the order associated with the transaction
     *
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
