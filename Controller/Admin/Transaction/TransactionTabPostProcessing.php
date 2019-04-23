<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin\Transaction;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Exception\StandardException;

use Wirecard\Oxid\Controller\Admin\Tab;
use Wirecard\Oxid\Model\Transaction;
use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\TransactionHandler;
use Wirecard\Oxid\Core\PaymentMethodFactory;
use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\PaymentSdk\Transaction\SofortTransaction;
use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Config\Config;

/**
 * Controls the view for the post-processing transaction tab.
 *
 * @since 1.0.0
 */
class TransactionTabPostProcessing extends Tab
{
    const KEY_ACTION = 'action';
    const KEY_AMOUNT = 'amount';

    /**
     * @var Transaction
     *
     * @since 1.0.0
     */
    protected $oTransaction;

    /**
     * @var \Psr\Log\LoggerInterface
     *
     * @since 1.0.1
     */
    private $_oLogger;

    /**
     * @inheritdoc
     *
     * @since 1.0.0
     */
    protected $_sThisTemplate = 'tab_post_processing.tpl';

    /**
     * @var TransactionHandler
     *
     * @since 1.0.1
     */
    private $_oTransactionHandler;

    /**
     * @var BackendService
     *
     * @since 1.0.1
     */
    private $_oBackendService;

    /**
     * array containing all possible post-processing actions for a transaction
     *
     * @since 1.0.1
     */
    private $_aPostProcessingActions;

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

        if ($this->_isListObjectIdSet()) {
            $this->oTransaction->load($this->sListObjectId);
        }
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
     * @since 1.0.1
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
     * Used in tests to mock the transaction handler
     *
     * @internal
     *
     * @param TransactionHandler $oTransactionHandler
     *
     * @since 1.0.1
     */
    public function setTransactionHandler($oTransactionHandler)
    {
        $this->_oTransactionHandler = $oTransactionHandler;
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function render()
    {
        $sTemplate = parent::render();

        $this->_aPostProcessingActions = [];

        // if the transaction state is 'closed', there are no post-processing actions available
        if ($this->oTransaction->wdoxidee_ordertransactions__state->value !== Transaction::STATE_CLOSED) {
            $this->_aPostProcessingActions = $this->_getPostProcessingActions();
        }

        $aRequestParameters = $this->_getRequestParameters();

        // use the maximum available amount of the transaction as default if there is no action to be processed
        if (empty($aRequestParameters[self::KEY_ACTION])) {
            $sTransactionId = $this->oTransaction->wdoxidee_ordertransactions__transactionid->value;
            $aRequestParameters[self::KEY_AMOUNT] =
                $this->_getTransactionHandler()->getTransactionMaxAmount($sTransactionId);
        }

        $this->setViewData([
            'actions' => $this->_aPostProcessingActions,
            'requestParameters' => $aRequestParameters,
            'alert' => $this->_processRequest($aRequestParameters),
            'currency' => $this->oTransaction->wdoxidee_ordertransactions__currency->value,
            'emptyText' => Helper::translate('wd_text_no_further_operations_possible'),
        ] + $this->getViewData());

        return $sTemplate;
    }

    /**
     * Returns an array of current request parameters.
     *
     * @return array
     *
     * @since 1.0.1
     */
    private function _getRequestParameters()
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
            self::KEY_AMOUNT => Helper::getFloatFromString($oConfig->getRequestParameter(self::KEY_AMOUNT) ?? ''),
            self::KEY_ACTION => $aActionConfig,
        ];
    }

    /**
     * Validates a request.
     *
     * @param array $aRequestParameters
     * @throws StandardException
     *
     * @since 1.0.1
     */
    private function _validateRequest($aRequestParameters)
    {
        $fAmount = $aRequestParameters[self::KEY_AMOUNT];

        if (!$this->_isAmountNumeric($fAmount)) {
            throw new StandardException(Helper::translate('wd_text_generic_error'));
        }

        $sTransactionId = $this->oTransaction->wdoxidee_ordertransactions__transactionid->value;
        $fMaxAmount = $this->_getTransactionHandler()->getTransactionMaxAmount($sTransactionId);

        if (!$this->_isPositiveBelowMax($fAmount, $fMaxAmount)) {
            throw new StandardException(Helper::translate('wd_total_amount_not_in_range_text'));
        }
    }

    /**
     * Checks that the amount argument is a numeric value
     *
     * @param float $fAmount
     *
     * @return boolean true if $fAmount is a numeric value
     *
     * @since 1.0.1
     */
    private function _isAmountNumeric($fAmount)
    {
        return $fAmount && (is_float($fAmount) || is_int($fAmount));
    }

    /**
     * Checks that the amount is in the range of the transaction
     *
     * @param float $fAmount
     * @param float $fMaxAmount
     *
     * @return boolean true if $fAmount is in the specified range
     *
     * @since 1.0.1
     */
    private function _isPositiveBelowMax($fAmount, $fMaxAmount)
    {
        return $fAmount > 0 && $fAmount <= $fMaxAmount;
    }

    /**
     * Processes a request and returns a state array if it is valid.
     *
     * @param array $aRequestParameters
     * @return array|null
     *
     * @since 1.0.1
     */
    private function _processRequest($aRequestParameters)
    {
        if (empty($aRequestParameters[self::KEY_ACTION])) {
            return null;
        }

        $aState = [];

        try {
            $this->_validateRequest($aRequestParameters);

            $sActionTitle = $aRequestParameters[self::KEY_ACTION][self::KEY_ACTION];
            $sTransactionAmount = $aRequestParameters[self::KEY_AMOUNT];

            // execute the callback method defined in the "action" request parameter
            $aState['message'] = $this->_handleRequestAction($sActionTitle, $sTransactionAmount);
            $aState['type'] = 'success';
        } catch (StandardException $e) {
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
     * @since 1.0.1
     */
    protected function _getPostProcessingActions()
    {
        $sPaymentId = $this->oTransaction->getPaymentType();

        // Need to create a transaction object with the ID of the currently selected one to get
        // the available post-processing operations from the Payment SDK
        $oPaymentMethod = PaymentMethodFactory::create($sPaymentId);
        $oTransaction = $oPaymentMethod->getTransaction();
        $sParentTransactionId = $this->oTransaction->wdoxidee_ordertransactions__transactionid->value;
        $oTransaction->setParentTransactionId($sParentTransactionId);

        $oConfig = $this->_getPaymentMethodConfig();
        $aPossibleOperations = $this->_getBackendService($oConfig)->retrieveBackendOperations($oTransaction, true);

        if ($aPossibleOperations === false || count($aPossibleOperations) <= 0) {
            return [];
        }

        $aPossibleOperations = $this->_filterPostProcessingActions($aPossibleOperations, $oPaymentMethod);

        return $this->_getTranslatedPostProcessingActions($aPossibleOperations);
    }

    /**
     * Filters the returned post processing actions for a payment method.
     * It is possible to do payment method specific modifications in this method.
     *
     * @param array         $aPossibleOperations
     * @param PaymentMethod $oPaymentMethod
     *
     * @return array
     *
     * @since 1.0.1
     */
    private function _filterPostProcessingActions($aPossibleOperations, $oPaymentMethod)
    {
        // currently there are no post-processing transactions for Sofort
        if ($oPaymentMethod->getName() === SofortTransaction::NAME) {
            return [];
        }

        return $aPossibleOperations;
    }

    /**
     * Applies the translate function to the supported post processing operations array.
     *
     * @param array $aSupportedOperations
     *
     * @return array
     *
     * @since 1.0.1
     */
    private function _getTranslatedPostProcessingActions($aSupportedOperations)
    {
        $aTranslatedActions = [];

        // this look-up array is necessary because of the PhraseApp integration
        $aOperationTitles = [
            BackendService::CANCEL_BUTTON_TEXT => Helper::translate('wd_cancel'),
            BackendService::REFUND_BUTTON_TEXT => Helper::translate('wd_refund'),
            BackendService::CAPTURE_BUTTON_TEXT => Helper::translate('wd_capture'),
            BackendService::CREDIT_BUTTON_TEXT => Helper::translate('wd_credit'),
        ];

        foreach ($aSupportedOperations as $sActionName => $sButtonText) {
            $aTranslatedActions[] = [
                'action' => $sActionName,
                'title' => $aOperationTitles[$sButtonText],
            ];
        }

        return $aTranslatedActions;
    }

    /**
     * Handles the request action by passing it on to the transaction handler
     *
     * @param string $sActionTitle
     * @param float  $fAmount
     *
     * @return string
     *
     * @since 1.0.1
     */
    private function _handleRequestAction($sActionTitle, $fAmount)
    {
        $oTransactionHandler = $this->_getTransactionHandler();
        $aResult = $oTransactionHandler->processAction($this->oTransaction, $sActionTitle, $fAmount);

        return $aResult["status"] === Transaction::STATE_SUCCESS ?
            Helper::translate('wd_text_generic_success') : $aResult['message'];
    }

    /**
     * Returns an instance of TransactionHandler (singleton)
     *
     * @return TransactionHandler
     *
     * @since 1.0.1
     */
    private function _getTransactionHandler()
    {
        if (is_null($this->_oTransactionHandler)) {
            $oConfig = $this->_getPaymentMethodConfig();
            $this->_oTransactionHandler = new TransactionHandler($this->_getBackendService($oConfig));
        }

        return $this->_oTransactionHandler;
    }

    /**
     * Returns the payment method config for the currently selected transaction or null if none is set.
     *
     * @return Config | null
     *
     * @since 1.0.1
     */
    private function _getPaymentMethodConfig()
    {
        $oConfig = null;

        if (!is_null($this->oTransaction)) {
            $sPaymentId = $this->oTransaction->getPaymentType();
            $oPayment = PaymentMethodHelper::getPaymentById($sPaymentId);
            $oPaymentMethod = PaymentMethodFactory::create($sPaymentId);

            $oConfig = $oPaymentMethod->getConfig($oPayment);
        }

        return $oConfig;
    }
}
