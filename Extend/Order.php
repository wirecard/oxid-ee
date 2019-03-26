<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend;

use Wirecard\Oxid\Model\Credit_Card_Payment_Method;
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

    private $aWirecardPaymentTypes = array(
        Paypal_Payment_Method::NAME,
        Credit_Card_Payment_Method::NAME
    );

    /**
     * Checks if the Paymenttype is one of wirecard's
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function isWirecardPaymentType()
    {
        return in_array($this->getPaymentType()->oxuserpayments__oxpaymentsid->value, $this->aWirecardPaymentTypes);
    }
}
