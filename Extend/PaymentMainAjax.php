<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend;

use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Config\Config;

use OxidEsales\Eshop\Core\Registry;

/**
 * Extends the AJAX handler of OXID's payment method configuration page
 *
 * @since 1.0.0
 */
class PaymentMainAjax extends PaymentMainAjax_parent
{
    /**
     * @var \Psr\Log\LoggerInterface
     *
     * @since 1.0.0
     */
    private $oLogger;

    /**
     * @var \OxidEsales\Eshop\Core\Util
     *
     * @since 1.0.0
     */
    private $oUtils;

    /**
     * @var \OxidEsales\Eshop\Core\Config
     *
     * @since 1.0.0
     */
    private $oConfig;

    /**
     * BasePaymentMain controller constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->oLogger = Registry::getLogger();
        $this->oUtils = Registry::getUtils();
        $this->oConfig = Registry::getConfig();
    }

    /**
     * Checks the validity of the payment method credentials the merchant set on the frontend.
     *
     * @since 1.0.0
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
