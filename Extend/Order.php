<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend;

use \Wirecard\Oxid\Model\Paypal_Payment_Method;

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
    public function isModulePaymentType(): bool
    {
        return in_array($this->getPaymentType()->oxuserpayments__oxpaymentsid->value, $this->_aModulePaymentTypes);
    }

    /**
     * Returns an array of available states.
     *
     * @return array
     */
    public static function getStates(): array
    {
        return [
            self::STATE_PENDING,
            self::STATE_AUTHORIZED,
            self::STATE_PROCESSING,
            self::STATE_CANCELED,
            self::STATE_REFUNDED,
        ];
    }
}
