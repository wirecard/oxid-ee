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
use Wirecard\Oxid\Model\PaymentMethod\CreditCardPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\BasePoiPiaPaymentMethod;
use Wirecard\Oxid\Model\PaymentMethod\PaymentMethod;
use Wirecard\Oxid\Model\Transaction;

use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Entity\Status;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;
use Wirecard\PaymentSdk\Response\FailureResponse;
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
    const MAX_TIMEOUT_SECONDS = 10;
    const ITERATION_TIMEOUT_SECONDS = 0.25;
    const MICROSECONDS_TO_SECONDS = 1000000;

    /**
     * @var LoggerInterface
     *
     * @since 1.0.0
     */
    private $_oLogger;

    /**
     * @var BackendService
     *
     * @since 1.1.0
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
     *
     * @since 1.1.0
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
     * @since 1.1.0
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
        } catch (InvalidArgumentException $oException) {
            $this->_oLogger->error(__METHOD__ . ': Invalid argument set: ' . $oException->getMessage(), [$oException]);
            return;
        } catch (MalformedResponseException $oException) {
            $this->_oLogger->error(__METHOD__ . ': Response is malformed: ' . $oException->getMessage(), [$oException]);
            return;
        }

        $this->_handleNotificationResponse($oNotificationResp, $oService, $sPaymentId);
    }

    /**
     * Handles the success and error response coming from the paymentSDK.
     *
     * @param Response       $oNotificationResp
     * @param BackendService $oBackendService
     * @param string         $sPaymentId
     *
     * @return null
     *
     * @since 1.0.0
     * @throws Exception
     */
    private function _handleNotificationResponse($oNotificationResp, $oBackendService, $sPaymentId)
    {
        // Return the response or log errors if any happen.
        if ($oNotificationResp instanceof SuccessResponse) {
            $oTransaction = oxNew(Transaction::class);

            if ($oTransaction->loadWithTransactionId($oNotificationResp->getTransactionId())) {
                // if a transaction with this ID already exists, we do not need to handle it again
                return;
            }

            $this->_onNotificationSuccess($oNotificationResp, $oBackendService, $sPaymentId);
            return;
        }

        $this->_onNotificationError($oNotificationResp);
    }

    /**
     * @param SuccessResponse $oResponse
     * @param BackendService  $oBackendService
     * @param string          $sPaymentId
     *
     * @return void
     *
     * @throws Exception
     *
     * @since 1.0.0
     */
    private function _onNotificationSuccess($oResponse, $oBackendService, $sPaymentId)
    {
        // check if the response of this transaction type should be handled or not
        $aExcludedTypes = [
            'check-payer-response',
        ];

        if (in_array($oResponse->getTransactionType(), $aExcludedTypes)) {
            return;
        }

        $sTransactionId = $oResponse->getParentTransactionId();
        // Ratepay Invoice and Payolution Invoice do not have a  parent transaction ID set
        if ($this->_shouldUseTransactionID($sTransactionId, $sPaymentId)) {
            $sTransactionId = $oResponse->getTransactionId();
        }

        $oOrder = oxNew(Order::class);
        $bSavedTransaction = self::_saveIfUnmatchedPoiPiaTransaction($sTransactionId, $oResponse, $oBackendService, $oOrder);

        if (!$bSavedTransaction) {
            ResponseHandler::onSuccessResponse($oResponse, $oBackendService, $oOrder);
        }
        return;
    }

    /**
     * Saves POI/PIA transaction if it is unmatched, and returns true. Otherwise returns false.
     *
     * @param string $sTransactionId
     * @param SuccessResponse $oResponse
     * @param BackendService  $oBackendService
     * @param Order $oOrder
     *
     * @return boolean
     *
     * @since 1.3.0
     */
    private function _saveIfUnmatchedPoiPiaTransaction($sTransactionId, $oResponse, $oBackendService, &$oOrder)
    {
        if ($this->_loadOrder($oOrder, $sTransactionId) >= self::MAX_TIMEOUT_SECONDS) {
            $this->_oLogger->error('No order found for transactionId: ' . $sTransactionId);

            // Unmatched transaction is saved only if it was a POI/PIA transaction
            if ($oResponse->getPaymentMethod() === BasePoiPiaPaymentMethod::PAYMENT_METHOD_WIRETRANSFER) {
                ResponseHandler::saveTransaction($oResponse, $oOrder, $oBackendService);
                return true;
            }

        }
        return false;
    }

    /**
     * Handles error notifications
     *
     * @param FailureResponse $oResponse
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

        $sParentTransactionId = $oResponse->getData()['parent-transaction-id'];

        if (!is_null($sParentTransactionId)) {
            $oOrder = oxNew(Order::class);
            $oOrder->loadWithTransactionId($sParentTransactionId);
            $oOrder->handleOrderState(Order::STATE_FAILED);
        }
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

    /**
     * Recursively tries to load the corresponding order
     * Needed because oxid some times(i.e non 3d credit card payments) does not create the order
     * before we get the success notification from the payment sdk
     *
     * @param Order  $oOrder
     * @param string $sTransactionId
     * @param int    $iIteration
     *
     * @return int   needed time in seconds to load order
     *
     * @since 1.3.0
     */
    private function _loadOrder(&$oOrder, $sTransactionId, $iIteration = 0)
    {
        $iNeededSeconds = $iIteration * self::ITERATION_TIMEOUT_SECONDS;

        if (!$oOrder->loadWithTransactionId($sTransactionId) && $iNeededSeconds < self::MAX_TIMEOUT_SECONDS) {
            usleep(self::ITERATION_TIMEOUT_SECONDS * self::MICROSECONDS_TO_SECONDS);

            $iIteration = $iIteration + 1;
            return $this->_loadOrder($oOrder, $sTransactionId, $iIteration);
        }

        return $iNeededSeconds;
    }

    /**
     * Returns true if transactionId should be used instead of parentTransactionId
     *
     * @param string $sTransactionId
     * @param string $sPaymentId
     *
     * @return bool
     *
     * @since 1.3.0
     */
    private function _shouldUseTransactionID($sTransactionId, $sPaymentId)
    {
        return is_null($sTransactionId) || $sPaymentId === CreditCardPaymentMethod::getName(true);
    }
}
