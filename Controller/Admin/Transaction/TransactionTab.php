<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin\Transaction;

use Wirecard\Oxid\Controller\Admin\Tab;
use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\ResponseMapper;
use Wirecard\Oxid\Model\Transaction;

use OxidEsales\Eshop\Application\Model\Payment;

/**
 * Controls the view for a single transaction tab.
 *
 * @since 1.0.0
 */
class TransactionTab extends Tab
{
    /**
     * @var Payment
     *
     * @since 1.0.0
     */
    protected $_oPayment;

    /**
     * @var ResponseMapper
     *
     * @since 1.0.0
     */
    protected $_oResponseMapper;

    // transaction state key in transaction response
    const KEY_TRANSACTION_STATE = 'transactionState';

    /**
     * TransactionTab constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->_oPayment = oxNew(Payment::class);

        if ($this->_isListObjectIdSet()) {
            $this->_oTransaction->load($this->_sListObjectId);
            $this->_oOrder->load($this->_oTransaction->wdoxidee_ordertransactions__orderid->value);
            $this->_oPayment->load($this->_oOrder->oxorder__oxpaymenttype->value);

            $this->_oResponseMapper = new ResponseMapper($this->_oTransaction->getResponseXML());
        }
    }

    /**
     * Transforms an associative array to a list data array.
     *
     * @param array  $aArray
     * @param string $sTransactionState
     * @param string $sTranslationPrefix
     * @return array
     *
     * @since 1.0.0
     */
    protected function _getListDataFromArray($aArray, $sTransactionState = null, $sTranslationPrefix = 'wd_')
    {
        $aListData = [];

        foreach ($aArray as $sKey => $sValue) {
            $aListData[] = [
                'title' => Helper::translate($sTranslationPrefix . $sKey),
                'value' => $this->_getTransactionStateText($sKey, $sValue, $sTransactionState),
            ];
        }

        return $aListData;
    }

    /**
     * Adds the current transaction state as a hint for the merchant if it differs from the response transaction state
     *
     * @param string $sKey
     * @param string $sValue
     * @param string $sTransactionState
     *
     * @return string the transaction state text
     *
     * @since 1.0.0
     */
    private function _getTransactionStateText($sKey, $sValue, $sTransactionState = null)
    {
        if ($sTransactionState &&
                $sKey === self::KEY_TRANSACTION_STATE &&
                $sTransactionState === Transaction::STATE_AWAITING) {
            $sValue .= ' (confirmation awaiting)';
        }

        return $sValue;
    }
}
