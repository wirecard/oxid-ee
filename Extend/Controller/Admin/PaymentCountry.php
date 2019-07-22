<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend\Controller\Admin;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Model\PaymentMethod\PayolutionInvoicePaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\RatepayInvoicePaymentMethod;

/**
 * Controls the view for the payment country tab.
 *
 * @since 1.2.0
 */
class PaymentCountry extends PaymentCountry_parent
{
    /**
     * @inheritdoc
     * @return string
     *
     * @since 1.2.0
     */
    public function render()
    {
        if (!$this->_allowCountryAssignment()) {
            Helper::addToViewData($this, [
                'readonly' => true,
            ]);
        }

        return parent::render();
    }

    /**
     * Checks if country assignment should be allowed for the current payment method.
     *
     * @return bool
     *
     * @since 1.2.0
     */
    protected function _allowCountryAssignment()
    {
        return !in_array($this->getEditObjectId(), [
            RatepayInvoicePaymentMethod::getName(),
            PayolutionInvoicePaymentMethod::getName(),
        ]);
    }
}
