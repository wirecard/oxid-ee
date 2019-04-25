<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin\Transaction;

use Exception;

use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Controller\Admin\Tab;
use Wirecard\Oxid\Model\Transaction;
use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\TransactionHandler;
use Wirecard\Oxid\Core\Payment_Method_Factory;

use \Wirecard\PaymentSdk\BackendService;

/**
 * Controls the view for the post-processing transaction tab.
 *
 * @since 1.0.0
 */
class TransactionTabPostProcessing extends Tab
{
    /**
     * @var Transaction
     *
     * @since 1.0.0
     */
    protected $oTransaction;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_oLogger;

    /**
     * @inheritdoc
     *
     * @since 1.0.0
     */
    protected $_sThisTemplate = 'tab_post_processing.tpl';

    /*
     * @var TransactionHandler
     */
    private $_oTransactionHandler = null;

    // array containing all possible post-processing actions for a transaction
    private $_aPostProcessingActions = null;

    /**
     * TransactionTab constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->oTransaction = oxNew(Transaction::class);

        $this->_oLogger = Registry::getLogger();

        $this->_aPostProcessingActions = array();

        if ($this->_isListObjectIdSet()) {
            $this->oTransaction->load($this->sListObjectId);
        }
    }

    /**
     * @inheritdoc
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function render()
    {
        $sTemplate = parent::render();

        $this->_aPostProcessingActions = $this->_getPostProcessingActions();

        $aRequestParameters = $this->_getRequestParameters();

        // use the maximum available amount of the transaction as default if there is no action to be processed
        if (empty($aRequestParameters['action'])) {
            $sTransactionId = $this->oTransaction->wdoxidee_ordertransactions__transactionid->value;
            $aRequestParameters['amount'] = $this->_getTransactionHandler()->getTransactionMaxAmount($sTransactionId);
        }

        $this->_aViewData += [
            'actions' => $this->_aPostProcessingActions,
            'requestParameters' => $aRequestParameters,
            'alert' => $this->_processRequest($aRequestParameters),
            'currency' => $this->oTransaction->wdoxidee_ordertransactions__currency->value,
            'noOperationsAvailableString' => Helper::translate('text_no_further_operations_possible')
        ];

        return $sTemplate;
    }

    /**
     * Returns an array of current request parameters.
     *
     * @return array
     *
     * @since 1.0.0
     */
    private function _getRequestParameters(): array
    {
        /**
         * @var OxidEsales\Eshop\Core\Config
         */
        $oConfig = Registry::getConfig();
        $aActionConfig = null;

        foreach ($this->_aPostProcessingActions as $sActionType => $aSingleActionConfig) {
            if ($oConfig->getRequestParameter($sActionType)) {
                $aActionConfig = $aSingleActionConfig;
            }
        }

        return [
            'amount' => Helper::getFloatFromString($oConfig->getRequestParameter('amount') ?? ''),
            'action' => $aActionConfig,
        ];
    }

    /**
     * Validates a request.
     *
     * @param array $aRequestParameters
     * @throws Exception
     *
     * @since 1.0.0
     */
    private function _validateRequest(array $aRequestParameters)
    {
        $fAmount = $aRequestParameters['amount'];

        if (!$this->_validateAmountIsNumeric($fAmount)) {
            throw new Exception(Helper::translate('text_generic_error'));
        }

        if (!$this->_validateAmountInRange($fAmount, $this->oTransaction->wdoxidee_ordertransactions__amount->value)) {
            throw new Exception(Helper::translate('total_amount_not_in_range_text'));
        }
    }

    /**
     * Checks that the amount argument is a numeric value
     *
     * @param float $fAmount
     *
     * @return boolean true/false whether amount argument is a numeric value
     */
    private function _validateAmountIsNumeric($fAmount)
    {
        return $fAmount && is_numeric($fAmount);
    }

    /**
     * Checks that the amount is in the range of the transaction
     *
     * @param float $fAmount
     * @param float $fTransactionAmount
     *
     * @return boolean true/false whether amount is in range
     */
    private function _validateAmountInRange($fAmount, $fTransactionAmount)
    {
        return $fAmount > 0 && $fAmount <= $fTransactionAmount;
    }

    /**
     * Processes a request and returns a state array if it is valid.
     *
     * @param array $aRequestParameters
     * @return array|null
     *
     * @since 1.0.0
     */
    private function _processRequest(array $aRequestParameters)
    {
        if (empty($aRequestParameters['action'])) {
            return null;
        }

        $aState = [];

        try {
            $this->_validateRequest($aRequestParameters);

            $sActionTitle = $aRequestParameters['action']['action'];
            $sTransactionAmount = $aRequestParameters['amount'];

            // execute the callback method defined in the "action" request parameter
            $aState['message'] = $this->_handleRequestAction($sActionTitle, $sTransactionAmount);
            $aState['type'] = 'success';
        } catch (Exception $e) {
            $aState['message'] = $e->getMessage();
            $aState['type'] = 'error';
        }

        return $aState;
    }

    /**
     * Returns an array of processing actions to be displayed below the amount input field.
     *
     * @throws Exception in case the payment method type is not found
     *
     * @return array
     *
     * @since 1.0.0
     */
    protected function _getPostProcessingActions(): array
    {
        // array containing all possible post-processing operations on the currently selected transaction
        $aOperations = array();

        // if the transaction state is 'closed', there are no post-processing acitons available
        if ($this->oTransaction->wdoxidee_ordertransactions__state->value !== Transaction::STATE_CLOSED) {
            // it is necessary to create a new empty transaction with the ID of the currently
            // selected one in order to get the available post-processing operations
            $oPaymentMethod = Payment_Method_Factory::create($this->oTransaction->getPaymentType());
            $oTransaction = $oPaymentMethod->getTransaction();
            $sParentTransactionId = $this->oTransaction->wdoxidee_ordertransactions__transactionid->value;
            $oTransaction->setParentTransactionId($sParentTransactionId);

            // load the supported operations array from the backend service
            $oBackendService = new BackendService($oPaymentMethod->getConfig(), $this->_oLogger);
            $aSupportedOperations = $oBackendService->retrieveBackendOperations($oTransaction, true);

            if ($aSupportedOperations !== false && count($aSupportedOperations > 0)) {
                // this look-up array is necessary because of the PhraseApp integration
                $aOperationTitles = array(
                    BackendService::CANCEL_BUTTON_TEXT => Helper::translate('cancel'),
                    BackendService::REFUND_BUTTON_TEXT => Helper::translate('refund'),
                    BackendService::CAPTURE_BUTTON_TEXT => Helper::translate('capture'),
                    BackendService::CREDIT_BUTTON_TEXT => Helper::translate('credit')
                );

                foreach ($aSupportedOperations as $sActionName => $sButtonText) {
                    $aOperations[] = [
                        'action' => $sActionName,
                        'title' => $aOperationTitles[$sButtonText],
                    ];
                }
            }
        }

        return $aOperations;
    }

    /**
     * Handles the request action by passing it on to the transaction handler
     *
     * @param string $sActionTitle
     * @param float  $fAmount
     *
     * @return string
     *
     * @since 1.0.0
     */
    private function _handleRequestAction($sActionTitle, $fAmount)
    {
        $oTransactionHandler = $this->_getTransactionHandler();
        $aResult = $oTransactionHandler->processAction($this->oTransaction, $sActionTitle, $fAmount);
        if ($aResult["status"] === TransactionHandler::TRANSACTION_STATUS_SUCCESS) {
            return Helper::translate('text_generic_success');
        } else {
            return $aResult['message'];
        }
    }

    /**
     * Returns an instance of TransactionHandler (singleton)
     *
     * @return TransactionHandler
     */
    private function _getTransactionHandler(): TransactionHandler
    {
        if (is_null($this->_oTransactionHandler)) {
            $this->_oTransactionHandler = new TransactionHandler();
        }

        return $this->_oTransactionHandler;
    }
}
