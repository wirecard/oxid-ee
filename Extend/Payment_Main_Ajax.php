<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace Wirecard\Oxid\Extend;

use \Wirecard\PaymentSdk\TransactionService;
use \Wirecard\PaymentSdk\Config\Config;

use \OxidEsales\Eshop\Core\Registry;

/**
 * Extends the AJAX handler of OXID's payment method confiuration page
 */
class Payment_Main_Ajax extends Payment_Main_Ajax_parent
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $oLogger;

    /**
     * @var \OxidEsales\Eshop\Core\Util
     */
    private $oUtils;

    /**
     * @var \OxidEsales\Eshop\Core\Config
     */
    private $oConfig;

    /**
     * BasePaymentMain controller constructor.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct()
    {
        $this->oLogger = Registry::getLogger();
        $this->oUtils = Registry::getUtils();
        $this->oConfig = Registry::getConfig();
    }

    /**
     * Checks the validity of the payment method credentials the merchant set on the frontend.
     */
    public function checkPaymentMethodCredentials()
    {
        $bSuccess = false;

        // get the parameters from the request
        $sApiUrl = $this->oConfig->getRequestParameter('apiUrl');
        $sHttpUser = $this->oConfig->getRequestParameter('httpUser');
        $sHttpPass = $this->oConfig->getRequestParameter('httpPass');

        // only perform the check if all parameters were sent
        if ($sApiUrl && $sHttpUser && $sHttpPass) {
            // use the paymentSDK transaction service to validate the credentials
            $oConfig = new Config($sApiUrl, $sHttpUser, $sHttpPass);
            $oTransactionService = new TransactionService($oConfig, $this->oLogger);
            $bSuccess = $oTransactionService->checkCredentials();
        }

        $this->oUtils->showMessageAndExit(json_encode(array("success" => $bSuccess)));
    }
}
