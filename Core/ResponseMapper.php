<?php

namespace Wirecard\Oxid\Core;

use Wirecard\PaymentSdk\Response\SuccessResponse;

/**
 * Converts an XML response to a Response object and provides various getters.
 */
class ResponseMapper
{
    /**
     * @var SuccessResponse
     */
    private $oResponse;

    /**
     * ResponseMapper constructor.
     *
     * @param string $sXml
     */
    public function __construct(string $sXml)
    {
        $this->setResponse($sXml);
    }

    /**
     * Response setter.
     *
     * @param string $sXml
     */
    public function setResponse(string $sXml): void
    {
        $this->oResponse = new SuccessResponse(simplexml_load_string($sXml));
    }

    /**
     * Returns the response's payment details.
     *
     * @return array
     */
    public function getPaymentDetails(): array
    {
        $oPaymentDetails = $this->oResponse->getPaymentDetails();

        return $oPaymentDetails ? $this->_parseHtml($oPaymentDetails->getAsHtml()) : [];
    }

    /**
     * Returns the response's transaction details.
     *
     * @return array
     */
    public function getTransactionDetails(): array
    {
        $oTransactionDetails = $this->oResponse->getTransactionDetails();

        return $oTransactionDetails ? $this->_parseHtml($oTransactionDetails->getAsHtml()) : [];
    }

    /**
     * Returns the response's account holder.
     *
     * @return array
     */
    public function getAccountHolder(): array
    {
        $oAccountHolder = $this->oResponse->getAccountHolder();

        return $oAccountHolder ? $this->_parseHtml($oAccountHolder->getAsHtml()) : [];
    }

    /**
     * Returns the response's shipping data.
     *
     * @return array
     */
    public function getShipping(): array
    {
        $oShipping = $this->oResponse->getShipping();

        return $oShipping ? $this->_parseHtml($oShipping->getAsHtml()) : [];
    }

    /**
     * Returns the response's basket.
     *
     * @return array
     */
    public function getBasket(): array
    {
        $oBasket = $this->oResponse->getBasket();

        return $oBasket ? $this->_parseHtml($oBasket->getAsHtml()) : [];
    }

    /**
     * Returns the response's card.
     *
     * @return array
     */
    public function getCard(): array
    {
        $oCard = $this->oResponse->getCard();

        return $oCard ? $this->_parseHtml($oCard->getAsHtml()) : [];
    }

    /**
     * Converts HTML returned by the SDK to an associative array.
     *
     * @param string $sHtml
     * @return array
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
