<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend;

use \Wirecard\PaymentSdk\TransactionService;
use \Wirecard\PaymentSdk\Config\Config;

use \OxidEsales\Eshop\Core\Registry;

/**
 * Extends the AJAX handler of OXID's payment method configuration page
 */
class PaymentMainAjax extends PaymentMainAjax_parent
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

        $this->oUtils->showMessageAndExit(json_encode(["success" => $bSuccess]));
    }
}
