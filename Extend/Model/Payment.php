<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend\Model;

use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Core\OxidEeEvents;
use Wirecard\Oxid\Core\PaymentMethodFactory;
use Wirecard\Oxid\Model\MetaDataModel;
use Wirecard\Oxid\Model\PaymentMethod\CreditCardPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\PaymentMethod;

/**
 * Extends the Payment model.
 *
 * @mixin \OxidEsales\Eshop\Application\Model\Payment
 *
 * @since 1.0.0
 */
class Payment extends Payment_parent
{
    use MetaDataModel;

    /**
     * @inheritdoc
     * @return string
     *
     * @since 1.2.0
     */
    public function getTableName()
    {
        return OxidEeEvents::PAYMENT_METADATA_TABLE;
    }

    /**
     * Checks if this is a current payment method.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function isCustomPaymentMethod()
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
        } catch (SystemComponentException $oException) {
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
    public function getLogoUrl()
    {
        $oPaymentMethod = $this->getPaymentMethod();

        if (!$oPaymentMethod) {
            return null;
        }

        return $oPaymentMethod->getLogoPath();
    }

    /**
     * Validate rendering the form
     *
     * @return bool
     *
     * @since 1.3.0
     */
    public function shouldRenderCreditCardForm()
    {
        return $this->oxpayments__oxid->value === CreditCardPaymentMethod::getName(true)
            && !CreditCardPaymentMethod::isCardTokenSet(Registry::getSession()->getVariable('dynvalue'));
    }
}
