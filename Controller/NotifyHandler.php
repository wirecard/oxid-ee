<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller;

use Exception;
use InvalidArgumentException;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;

use Psr\Log\LoggerInterface;

use Wirecard\Oxid\Core\PaymentMethodFactory;
use Wirecard\Oxid\Core\ResponseHandler;
use Wirecard\Oxid\Extend\Model\Order;
use Wirecard\Oxid\Model\PaymentMethod;
use Wirecard\Oxid\Model\Transaction;

use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Entity\Status;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Response\SuccessResponse;

/**
 * Notify handler class.
 *
 * Handle Payment SDK notifications.
 *
 * @since 1.0.0
 */
class NotifyHandler extends FrontendController
{
    /**
     * @var LoggerInterface
     *
     * @since 1.0.0
     */
    private $_oLogger;

    /**
     * @var BackendService
     *
     * @since 1.0.1
     */
    private $_oBackendService;

    /**
     * NotifyHandler constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->_oLogger = Registry::getLogger();
    }

    /**
     * Return the Backend service
     *
     * NOTE: for testing use the setter to inject the {@link BackendService}
     *
     * @param Config $oConfig
     *
     * @return BackendService
     */
    private function _getBackendService($oConfig)
    {
        if (is_null($this->_oBackendService)) {
            $this->_oBackendService = new BackendService($oConfig, $this->_oLogger);
        }

        return $this->_oBackendService;
    }

    /**
     * Used in tests to mock the backend service
     *
     * @internal
     *
     * @param BackendService $oBackendService
     *
     * @since 1.0.1
     */
    public function setBackendService($oBackendService)
    {
        $this->_oBackendService = $oBackendService;
    }

    /**
     * Request handling function.
     *
     * @return void
     * @throws StandardException if $sPaymentName does not exist
     *
     * @since 1.0.0
     */
    public function handleRequest()
    {
        $sPaymentId = Registry::getRequest()->getRequestParameter('pmt');

        $oPaymentMethod = PaymentMethodFactory::create($sPaymentId);

        $oConfig = $oPaymentMethod->getConfig();
        $sPostData = file_get_contents('php://input');

        try {
            $oService = $this->_getBackendService($oConfig);
            $oNotificationResp = $oService->handleNotification($sPostData);
        } catch (InvalidArgumentException $exception) {
            $this->_oLogger->error(__METHOD__ . ': Invalid argument set: ' . $exception->getMessage(), [$exception]);
            return;
        } catch (MalformedResponseException $oException) {
            $this->_oLogger->error(__METHOD__ . ': Response is malformed: ' . $oException->getMessage(), [$oException]);
            return;
        }

        $this->_handleNotificationResponse($oNotificationResp, $oService);
    }

    /**
     * Handles the success and error response coming from the paymentSDK.
     *
     * @param Response       $oNotificationResp
     * @param BackendService $oBackendService
     *
     * @return null
     *
     * @since 1.0.0
     */
    private function _handleNotificationResponse($oNotificationResp, $oBackendService)
    {
        // Return the response or log errors if any happen.
        if ($oNotificationResp instanceof SuccessResponse) {
            $oTransaction = oxNew(Transaction::class);

            if ($oTransaction->loadWithTransactionId($oNotificationResp->getTransactionId())) {
                // if a transaction with this ID already exists, we do not need to handle it again
                return;
            }
            $this->_onNotificationSuccess($oNotificationResp, $oBackendService);

            return;
        }

        $this->_onNotificationError($oNotificationResp);
    }

    /**
     * @param SuccessResponse $oResponse
     * @param BackendService  $oBackendService
     *
     * @return void
     *
     * @throws Exception
     *
     * @since 1.0.0
     */
    private function _onNotificationSuccess($oResponse, $oBackendService)
    {
        // check if the response of this transaction type should be handled or not
        $aExcludedTypes = [
            'check-payer-response',
        ];

        if (in_array($oResponse->getTransactionType(), $aExcludedTypes)) {
            return;
        }

        $oOrder = oxNew(Order::class);
        if (!$oOrder->loadWithTransactionId($oResponse->getParentTransactionId())) {
            $this->_oLogger->error('No order found for transactionId: ' . $oResponse->getParentTransactionId());
            return;
        }

        ResponseHandler::onSuccessResponse($oResponse, $oBackendService, $oOrder);
    }

    /**
     * Handles error notifications
     *
     * @param Response $oResponse
     *
     * @since 1.0.0
     */
    private function _onNotificationError($oResponse)
    {
        $this->_oLogger->error(__METHOD__ . ': Error processing transaction:');

        foreach ($oResponse->getStatusCollection() as $oStatus) {
            /**
             * @var Status $oStatus
             */
            $sSeverity = ucfirst($oStatus->getSeverity());
            $sCode = $oStatus->getCode();
            $sDescription = $oStatus->getDescription();
            $this->_oLogger->error("\t$sSeverity with code $sCode and message '$sDescription' occurred.");
        }

        // FIXME Failure response does not have a parent transaction id
//        $oOrder = oxNew(Order::class);
//        $oOrder->loadWithTransactionId($oResponse->getParentTransactionId());
//        $oOrder->handleOrderState(Order::STATE_FAILED);
    }

    /**
     * Returns the URL of the notification handler
     *
     * @param PaymentMethod $oPaymentMethod
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function getNotificationUrl($oPaymentMethod)
    {
        $sShopUrl = Registry::getConfig()->getCurrentShopUrl();

        return $sShopUrl
            . 'index.php?cl=wcpg_notifyhandler&fnc=handleRequest&pmt='
            . PaymentMethod::getOxidFromSDKName($oPaymentMethod->getTransaction()->getConfigKey());
    }
}
