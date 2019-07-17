<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model\PaymentMethod;

/**
 * Payment method implementation for Payment on Invoice
 *
 * @since 1.3.0
 */
class PaymentOnInvoicePaymentMethod extends BasePoiPiaPaymentMethod
{
    /**
     * @inheritdoc
     *
     * @since 1.3.0
     */
    protected static $_sName = "paymentoninvoice";
}
