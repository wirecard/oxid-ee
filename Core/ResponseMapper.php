<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Card;
use Wirecard\PaymentSdk\Entity\PaymentDetails;
use Wirecard\PaymentSdk\Entity\TransactionDetails;
use Wirecard\PaymentSdk\Response\SuccessResponse;

/**
 * Converts an XML response to a Response object and provides various getters.
 *
 * @since 1.0.0
 */
class ResponseMapper
{
    /**
     * @var SuccessResponse
     *
     * @since 1.0.0
     */
    private $_oResponse;

    /**
     * ResponseMapper constructor.
     *
     * @param string $sXml
     *
     * @since 1.0.0
     */
    public function __construct($sXml)
    {
        $this->_oResponse = new SuccessResponse(simplexml_load_string($sXml));
    }

    /**
     * Returns the response
     *
     * @return SuccessResponse
     *
     * @since 1.1.0
     */
    public function getResponse()
    {
        return $this->_oResponse;
    }

    /**
     * Returns the response's payment details.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getPaymentDetails()
    {
        return $this->_getObjectDataArray($this->_oResponse->getPaymentDetails());
    }

    /**
     * Returns the response's transaction details.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getTransactionDetails()
    {
        return $this->_getObjectDataArray($this->_oResponse->getTransactionDetails());
    }

    /**
     * Returns the response's account holder.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getAccountHolder()
    {
        return $this->_getObjectDataArray($this->_oResponse->getAccountHolder());
    }

    /**
     * Returns the response's shipping data.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getShipping()
    {
        return $this->_getObjectDataArray($this->_oResponse->getShipping());
    }

    /**
     * Returns the response's basket.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getBasket()
    {
        return $this->_getObjectDataArray($this->_oResponse->getBasket());
    }

    /**
     * Returns the response's card.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getCard()
    {
        return $this->_getObjectDataArray($this->_oResponse->getCard());
    }

    /**
     * Returns the whole data from the response xml.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getData()
    {
        return $this->_oResponse->getData();
    }

    /**
     * Returns the whole data from the response xml in a human-readable form.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getDataReadable()
    {
        $aResponseData = $this->_oResponse->getData();

        $aSortKeys = [
            'payment-methods.0.name',
            'order-number',
            'request-id',
            'transaction-id',
            'transaction-state',
            'statuses.0.provider-transaction-id',
        ];

        $aRestOfKeys = array_diff(array_keys($aResponseData), $aSortKeys);
        $aSortedKeys = array_merge($aSortKeys, $aRestOfKeys);

        $aList = [];

        foreach ($aSortedKeys as $sKey) {
            $aList[] = [
                'title' => Helper::translate($this->_mapReponseKey($sKey)),
                'value' => $aResponseData[$sKey] ?? null,
            ];
        }

        return $aList;
    }

    /**
     * Returns an array with the given object's data
     *
     * @param PaymentDetails|TransactionDetails|AccountHolder|Basket|Card $oResponseObject
     *
     * @return array
     *
     * @since 1.0.0
     */
    private function _getObjectDataArray($oResponseObject)
    {
        return $oResponseObject ? $this->_parseHtml($oResponseObject->getAsHtml()) : [];
    }

    /**
     * Converts HTML returned by the SDK to an associative array.
     *
     * @param string $sHtml
     *
     * @return array
     *
     * @since 1.0.0
     */
    private function _parseHtml($sHtml)
    {
        $aFields = [];
        preg_match_all('/<tr><td>(.+?)<\/td><td>(.+?)<\/td><\/tr>/', $sHtml, $aMatches, PREG_SET_ORDER);

        if (!$aMatches) {
            return $aFields;
        }

        foreach ($aMatches as $aMatch) {
            $aFields[$aMatch[1]] = $aMatch[2];
        }

        return $aFields;
    }

    /**
     * Maps the response key to PhraseApp key for a nice display of the transaction response details.
     *
     * @param string $sKey
     *
     * @return string
     *
     * @since 1.2.0
     */
    private function _mapReponseKey($sKey)
    {
        $aMappedKeys = [
            'currency' => 'wd_panel_currency',
            'descriptor' => 'wd_config_descriptor',
            'merchant-account-id' => 'wd_maid',
            'order-number' => 'wd_orderNumber',
            'parent-transaction-id' => 'wd_panel_parent_transaction_id',
            'payment-methods.0.name' => 'wd_panel_payment_method',
            'request-id' => 'wd_requestId',
            'requested-amount' => 'wd_requestedAmount',
            'statuses.0.provider-transaction-id' => 'wd_panel_provider_transaction_id',
            'transaction-id' => 'wd_transactionID',
            'transaction-state' => 'wd_transactionState',
            'transaction-type' => 'wd_transactionType',
        ];

        return $aMappedKeys[$sKey] ?? $sKey;
    }
}
