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

use \Wirecard\Oxid\Core\Payment_Method_Factory;
use \Wirecard\PaymentSdk\Entity\Amount;
use \Wirecard\PaymentSdk\TransactionService;
use \Wirecard\PaymentSdk\Response\FailureResponse;
use \Wirecard\PaymentSdk\Response\InteractionResponse;
use \Wirecard\PaymentSdk\Entity\Redirect;

use \OxidEsales\Eshop\Core\Registry;

/**
 * Class BasePaymentGateway
 *
 * Base class for all payment methods
 *
 * @mixin  \OxidEsales\Eshop\Application\Model\PaymentGateway
 *
 */
class Payment_Gateway extends Payment_Gateway_parent
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $oLogger;

    /**
     * @var \OxidEsales\Eshop\Core\Language
     */
    private $oLang;

    /**
     * BasePaymentGateway constructor.
     */
    public function __construct()
    {
        $this->oLogger = Registry::getLogger();
        $this->oLang = Registry::getLang();
    }

    /**
     * Executes payment, returns true on success.
     *
     * @param double $dAmount Goods amount
     * @param \Wirecard\Oxid\Extend\Order $oOrder User ordering object
     *
     * @return bool
     *
     * @override
     */
    public function executePayment($dAmount, &$oOrder)
    {
        if (!$oOrder->isWirecardPaymentType()) {
            return parent::executePayment($dAmount, $oOrder);
        }

        $sShopUrl = $this->getConfig()->getCurrentShopUrl();
        $oSession = $this->getSession();

        $sSid = $oSession->sid(true);
        if ($sSid != '') {
            $sSid = '&' . $sSid;
        }

        $sErrorText = $this->oLang->translateString('order_error');

        $oRedirect = new Redirect(
            $sShopUrl . 'index.php?cl=thankyou' . $sSid,
            $sShopUrl . 'index.php?type=cancel&cl=payment',
            $sShopUrl . 'index.php?type=error&cl=payment&errortext=' . urlencode($sErrorText)
        );

        $oResponse = null;

        try {
            $oPaymentMethod = Payment_Method_Factory::create($oOrder->getPaymentType());
            $oTransactionService = new TransactionService($oPaymentMethod->getConfig(), $$this->oLogger);

            $oTransaction = $oPaymentMethod->getTransaction();
            $oTransaction->setRedirect($oRedirect);
            $oTransaction->setAmount(new Amount($dAmount, $oOrder->sgetOrderCurrency()));
            $oTransaction->setNotificationUrl($sShopUrl . 'notify.php');

            $oResponse = $oTransactionService->pay($oTransaction);
        } catch (\Exception $exc) {
            $this->oLogger->error("Error processing transaction", [$exc]);
            return false;
        }

        if ($oResponse instanceof FailureResponse) {
            $this->oLogger->error('Error processing transaction:');

            foreach ($oResponse->getStatusCollection() as $oStatus) {
                /**
                 * @var $oStatus \Wirecard\PaymentSdk\Entity\Status
                 */
                $sSeverity = ucfirst($oStatus->getSeverity());
                $sCode = $oStatus->getCode();
                $sDescription = $oStatus->getDescription();
                $this->oLogger->error("\t$sSeverity with code $sCode and message '$sDescription' occurred.");
            }
            return false;
        }

        $sPageUrl = null;

        if ($oResponse instanceof InteractionResponse) {
            $sPageUrl = $oResponse->getRedirectUrl();
        }

        Registry::getUtils()->redirect($sPageUrl);
        return true;
    }
}
