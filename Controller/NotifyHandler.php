<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller;

use Wirecard\Oxid\Core\PaymentMethodFactory;
use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\Oxid\Core\ResponseHandler;
use Wirecard\Oxid\Extend\Model\Order;
use Wirecard\Oxid\Model\Transaction;
use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Entity\Status;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;
use Wirecard\PaymentSdk\Response\SuccessResponse;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Registry;

use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Exception;

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
     * Request handling function.
     *
     * @return void
     * @throws Exception if $sPaymentName does not exist
     *
     * @since 1.0.0
     */
    public function handleRequest()
    {
        $sPaymentId = Registry::getRequest()->getRequestParameter('pmt');
        $oPayment = PaymentMethodHelper::getPaymentById($sPaymentId);

        $oPaymentMethod = PaymentMethodFactory::create($sPaymentId);

        $oConfig = $oPaymentMethod->getConfig($oPayment);
        $sPostData = file_get_contents('php://input');

        try {
            $oService = new BackendService($oConfig, $this->_oLogger);
            $oNotificationResponse = $oService->handleNotification($sPostData);
        } catch (InvalidArgumentException $exception) {
            $this->_oLogger->error(__METHOD__ . ': Invalid argument set: ' . $exception->getMessage(), [$exception]);
            return;
        } catch (MalformedResponseException $exception) {
            $this->_oLogger->error(__METHOD__ . ': Response is malformed: ' . $exception->getMessage(), [$exception]);
            return;
        }

        $oTransaction = oxNew(Transaction::class);

        if ($oTransaction->loadWithTransactionId($oNotificationResponse->getTransactionId())) {
            // if a transaction with this ID already exists, we do not need to handle it again
            return;
        }

        // Return the response or log errors if any happen.
        if ($oNotificationResponse instanceof SuccessResponse && $oNotificationResponse->isValidSignature()) {
            $this->_onNotificationSuccess($oNotificationResponse, $oService);
        } else {
            $this->_onNotificationError($oNotificationResponse);
        }
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
    private function _onNotificationError(Response $oResponse)
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

        $oOrder = oxNew(Order::class);
        $oOrder->loadWithTransactionId($oResponse->getParentTransactionId());
        $oOrder->handleOrderState(Order::STATE_FAILED);
    }
}
