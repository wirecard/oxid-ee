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

use OxidEsales\Eshop\Core\Registry;

use Exception;

/**
 * Extends the Payment model.
 */
class Payment extends Payment_parent
{
    /**
     * Checks if this is a current payment method.
     *
     * @return bool
     */
    public function isCustomPaymentMethod(): bool
    {
        return !!$this->oxpayments__wdoxidee_isours->value;
    }

    /**
     * Returns the PaymentMethod object associated with the payment method.
     *
     * @return PaymentMethod|null
     */
    public function getPaymentMethod()
    {
        try {
            return PaymentMethodFactory::create($this->getId());
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Returns the logo URL of the payment method or null, if none is specified.
     *
     * @return string|null
     */
    public function getLogoUrl(): ?string
    {
        if (!$this->oxpayments__wdoxidee_logo->value) {
            return null;
        }

        $oConfig = Registry::getConfig();

        return $oConfig->getShopUrl() . $oConfig->getModulesDir(false) .
            "wirecard/paymentgateway/out/img/{$this->oxpayments__wdoxidee_logo->value}";
    }
}
