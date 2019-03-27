<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use \OxidEsales\Eshop\Application\Model\Payment;
use \OxidEsales\Eshop\Application\Model\UserPayment;
use \OxidEsales\Eshop\Core\Registry;

use \Wirecard\PaymentSdk\Config\Config;
use \Wirecard\PaymentSdk\Transaction\Transaction;

/**
 * Class Payment_Method
 *
 * @package Wirecard\Model
 */
abstract class Payment_Method
{
    /**
     * Get the payments method transaction configuration
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    abstract public function getTransaction(): Transaction;

    /**
     * Get the payments method configuration
     *
     * @param Payment $oPayment
     *
     * @return Config
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function getConfig(Payment $oPayment): Config
    {
        $oConfig = new Config(
            $oPayment->oxpayments__wdoxidee_apiurl->value,
            $oPayment->oxpayments__wdoxidee_httpuser->value,
            $oPayment->oxpayments__wdoxidee_httppass->value
        );

        return $oConfig;
    }
}
