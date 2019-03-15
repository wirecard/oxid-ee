<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

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
     */
    abstract public function getTransaction(): Transaction;

    /**
     * Get the payments method configuration
     */
    abstract public function getConfig(): Config;
}
