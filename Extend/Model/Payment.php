<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend\Model;

use Wirecard\Oxid\Core\PaymentMethodFactory;
use Wirecard\Oxid\Model\PaymentMethod;

use OxidEsales\Eshop\Core\Exception\StandardException;

/**
 * Extends the Payment model.
 *
 * @since 1.0.0
 */
class Payment extends Payment_parent
{
    /**
     * Checks if this is a current payment method.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function isCustomPaymentMethod(): bool
    {
        return !!$this->oxpayments__wdoxidee_isours->value;
    }

    /**
     * Returns the PaymentMethod object associated with the payment method.
     *
     * @return PaymentMethod|null
     *
     * @since 1.0.0
     */
    public function getPaymentMethod()
    {
        try {
            return PaymentMethodFactory::create($this->getId());
        } catch (StandardException $e) {
            return null;
        }
    }

    /**
     * Returns the logo URL of the payment method or null, if none is specified.
     *
     * @return string|null
     *
     * @since 1.0.0
     */
    public function getLogoUrl(): ?string
    {
        $oPaymentMethod = $this->getPaymentMethod();

        if (!$oPaymentMethod) {
            return null;
        }

        return $oPaymentMethod->getLogoPath($this);
    }
}
