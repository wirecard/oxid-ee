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

use \OxidEsales\Eshop\Core\Registry;

use \Psr\Log\LoggerInterface;
use \Exception;

/**
 * Class Payment_Method
 *
 * @package Wirecard\Model
 */
abstract class Payment_Method
{
    const OXID_NAME_PREFIX = 'wd';

    /**
     * @var string
     */
    protected static $_sName = 'undefined';

    /**
     * @var LoggerInterface
     */
    protected $_oLogger;

    /**
     * Paypal_Payment_Method constructor.
     *
     * @throws Exception if payment method name is not overwritten in child class
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public function __construct()
    {
        $this->_oLogger = Registry::getLogger();

        if ($this::$_sName == 'undefined') {
            throw new Exception("payment method name not defined: " . get_class());
        }
    }

    /**
     * Get the payments method transaction configuration
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    abstract public function getTransaction(): Transaction;

    /**
     * Get the payments method configuration
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    abstract public function getConfig(): Config;

    /**
     * Get the payment methods name
     *
     * @param bool $bForOxid
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Coverage)
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public static function getName(bool $bForOxid = false): string
    {
        $childClass = get_called_class();

        if ($bForOxid) {
            return self::OXID_NAME_PREFIX . $childClass::$_sName;
        }

        return $childClass::$_sName;
    }

    /**
     * Returns the prefixed Oxid payment method name
     *
     * @param string $sSDKName
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Coverage)
     */
    public static function getOxidFromSDKName(string $sSDKName): string
    {
        return self::OXID_NAME_PREFIX . $sSDKName;
    }
}
