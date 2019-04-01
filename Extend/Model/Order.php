<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend\Model;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Model\Paypal_Payment_Method;

/**
 * Class Order
 *
 * @package Wirecard\Extend
 *
 * @mixin \OxidEsales\Eshop\Application\Model\Order
 */
class Order extends Order_parent
{
    const STATE_PENDING = 'pending';
    const STATE_AUTHORIZED = 'authorized';
    const STATE_PROCESSING = 'processing';
    const STATE_CANCELED = 'canceled';
    const STATE_REFUNDED = 'refunded';

    private $_aModulePaymentTypes = array();

    /**
     * Order constructor.
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function __construct()
    {
        parent::__construct();

        $this->_aModulePaymentTypes[] = Paypal_Payment_Method::getName(true);
    }

    /**
     * Checks if the Paymenttype is one of the module's
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function isModulePaymentType()
    {
        return in_array($this->getPaymentType()->oxuserpayments__oxpaymentsid->value, $this->_aModulePaymentTypes);
    }

    /**
     * Returns an associative array of available states and their translation.
     *
     * @return array
     */
    public static function getTranslatedStates(): array
    {
        return [
            self::STATE_PENDING => Helper::translate('order_status_pending'),
            self::STATE_AUTHORIZED => Helper::translate('order_status_authorized'),
            self::STATE_PROCESSING => Helper::translate('order_status_purchased'),
            self::STATE_CANCELED => Helper::translate('order_status_cancelled'),
            self::STATE_REFUNDED => Helper::translate('order_status_refunded'),
        ];
    }

    /**
     * Returns an array of available states.
     *
     * @return array
     */
    public static function getStates(): array
    {
        return array_keys(self::getTranslatedStates());
    }

    /**
     * Returns the translation for the order's state.
     *
     * @return string
     */
    public function getTranslatedState(): string
    {
        return self::getTranslatedStates()[$this->oxorder__wdoxidee_orderstate->value] ?? '';
    }
}
