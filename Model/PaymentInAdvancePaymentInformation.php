<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

/**
 * Wrapper class for handling payment information for Payment in Advance
 *
 * @since 1.3.0
 */
class PaymentInAdvancePaymentInformation
{
    /**
     * @var string
     *
     * @since 1.3.0
     */
    public $sAmount;

    /**
     * @var string
     *
     * @since 1.3.0
     */
    public $sIban;

    /**
     * @var string
     *
     * @since 1.3.0
     */
    public $sBic;

    /**
     * @var string
     *
     * @since 1.3.0
     */
    public $sTransactionRefId;

    /**
     * PaymentInAdvancePaymentInformation constructor.
     *
     * @param string $sAmount
     * @param string $sIban
     * @param string $sBic
     * @param string $sTransactionRefId
     *
     * @since 1.3.0
     */
    public function __construct($sAmount, $sIban, $sBic, $sTransactionRefId)
    {
        $this->sAmount = $sAmount;
        $this->sIban = $sIban;
        $this->sBic = $sBic;
        $this->sTransactionRefId = $sTransactionRefId;
    }
}
