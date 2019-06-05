<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend;

use OxidEsales\Eshop\Core\Registry;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\TransactionService;

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
    private $_oLogger;

    /**
     * @var \OxidEsales\Eshop\Core\Util
     *
     * @since 1.0.0
     */
    private $_oUtils;

    /**
     * BasePaymentMain controller constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->_oLogger = Registry::getLogger();
        $this->_oUtils = Registry::getUtils();
        $this->_oConfig = Registry::getConfig();
    }

    /**
     * Checks the validity of the payment method credentials the merchant set on the frontend.
     *
     * @return bool
     *
     * @throws \Http\Client\Exception
     *
     * @since 1.0.0
     */
    public function checkPaymentMethodCredentials()
    {
        $bSuccess = false;

        // get the parameters from the request
        $sApiUrl = $this->_oConfig->getRequestParameter('apiUrl');
        $sHttpUser = $this->_oConfig->getRequestParameter('httpUser');
        $sHttpPass = $this->_oConfig->getRequestParameter('httpPass');

        // only perform the check if all parameters were sent
        if ($sApiUrl && $sHttpUser && $sHttpPass) {
            // use the paymentSDK transaction service to validate the credentials
            $oConfig = new Config($sApiUrl, $sHttpUser, $sHttpPass);
            $oTransactionService = new TransactionService($oConfig, $this->_oLogger);
            $bSuccess = $oTransactionService->checkCredentials();
        }

        return $this->_oUtils->showMessageAndExit(json_encode(["success" => $bSuccess]));
    }
}
