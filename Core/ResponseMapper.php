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
    private $oResponse;

    /**
     * ResponseMapper constructor.
     *
     * @param string $sXml
     *
     * @since 1.0.0
     */
    public function __construct(string $sXml)
    {
        $this->oResponse = new SuccessResponse(simplexml_load_string($sXml));
    }

    /**
     * Returns the response's payment details.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getPaymentDetails(): array
    {
        return $this->_getObjectDataArray($this->oResponse->getPaymentDetails());
    }

    /**
     * Returns the response's transaction details.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getTransactionDetails(): array
    {
        return $this->_getObjectDataArray($this->oResponse->getTransactionDetails());
    }

    /**
     * Returns the response's account holder.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getAccountHolder(): array
    {
        return $this->_getObjectDataArray($this->oResponse->getAccountHolder());
    }

    /**
     * Returns the response's shipping data.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getShipping(): array
    {
        return $this->_getObjectDataArray($this->oResponse->getShipping());
    }

    /**
     * Returns the response's basket.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getBasket(): array
    {
        return $this->_getObjectDataArray($this->oResponse->getBasket());
    }

    /**
     * Returns the response's card.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getCard(): array
    {
        return $this->_getObjectDataArray($this->oResponse->getCard());
    }

    /**
     * Returns the whole data from the response xml.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getData(): array
    {
        return $this->oResponse->getData();
    }

    /**
     * Returns the whole data from the response xml in a human-readable form.
     *
     * @return array
     */
    public function getDataReadable(): array
    {
        $aResponseData = $this->oResponse->getData();

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
                'title' => $sKey,
                'value' => $aResponseData[$sKey] ?? null,
            ];
        }

        return $aList;
    }

    /**
     * Returns an array with the given object's data
     *
     * @param PaymentDetails|TransactionDetails|AccountHolder|Basket|Card $oResponseObject
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
     * @return array
     *
     * @since 1.0.0
     */
    private function _parseHtml(string $sHtml): array
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
}
