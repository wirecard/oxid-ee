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

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\Oxid\Core\TransactionHandler;
use Wirecard\Oxid\Core\PaymentMethodFactory;
use Wirecard\Oxid\Extend\Model\Payment;
use Wirecard\Oxid\Model\Transaction;
use Wirecard\Oxid\Model\PaymentMethod;

use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Config\Config;

/**
 * Controls the view for the post-processing transaction tab.
 *
 * @since 1.0.0
 */
class TransactionTabPostProcessing extends TransactionTab
{
    const KEY_ACTION = 'action';
    const KEY_AMOUNT = 'amount';
    const KEY_STATUS = 'status';
    const KEY_TYPE = 'type';
    const KEY_MESSAGE = 'message';
    const KEY_INFO = 'info';
    const KEY_SUCCESS = 'success';
    const KEY_ERROR = 'error';

    /**
     * @var \Psr\Log\LoggerInterface
     *
     * @since 1.1.0
     */
    private $_oLogger;

    /**
     * @inheritdoc
     *
     * @since 1.0.0
     */
    protected $_sThisTemplate = 'tab_post_processing.tpl';

    /**
     * NOTE: for testing use the setter to inject the {@link TransactionHandler}
     * @var TransactionHandler
     *
     * @since 1.1.0
     */
    private $_oTransactionHandler;

    /**
     * NOTE: for testing use the setter to inject the {@link BackendService}
     * @var BackendService
     *
     * @since 1.1.0
     */
    private $_oBackendService;

    /**
     * array containing all possible post-processing actions for a transaction
     *
     * @since 1.1.0
     */
    private $_aActions;

    /**
     * TransactionTab constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct();

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
     * @since 1.1.0
     */
    private function _getBackendService($oConfig)
    {
        // NOTE: if _oBackendService got injected for testing, use it
        if ($this->_oBackendService) {
            return $this->_oBackendService;
        }

        return new BackendService($oConfig, $this->_oLogger);
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
     * Used in tests to mock the transaction handler
     *
     * @internal
     *
     * @param TransactionHandler $oTransactionHandler
     *
     * @since 1.1.0
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

        $this->_aActions = [];

        // if the transaction state is 'closed', there are no post-processing actions available
        if ($this->oTransaction->wdoxidee_ordertransactions__state->value !== Transaction::STATE_CLOSED) {
            $this->_aActions = $this->_getPostProcessingActions();
        }

        $aRequestParameters = $this->_getRequestParameters();

        // use the maximum available amount of the transaction as default if there is no action to be processed
        if (empty($aRequestParameters[self::KEY_ACTION])) {
            $sTransactionId = $this->oTransaction->wdoxidee_ordertransactions__transactionid->value;
            $aRequestParameters[self::KEY_AMOUNT] =
                $this->_getTransactionHandler()->getTransactionMaxAmount($sTransactionId);
        }

        Helper::addToViewData($this, [
            'actions' => $this->_aActions,
            'requestParameters' => $aRequestParameters,
            'message' => $this->_processRequest($aRequestParameters),
            'currency' => $this->oTransaction->wdoxidee_ordertransactions__currency->value,
            'emptyText' => Helper::translate('wd_text_no_further_operations_possible'),
        ]);

        return $sTemplate;
    }

    /**
     * Returns an array of current request parameters.
     *
     * @return array
     *
     * @since 1.1.0
     */
    private function _getRequestParameters()
    {
        /**
         * @var OxidEsales\Eshop\Core\Config
         */
        $oConfig = Registry::getConfig();
        $aActionConfig = null;

        foreach ($this->_aActions as $sActionType => $aSingleActionConfig) {
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
     * @since 1.1.0
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
     * @since 1.1.0
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
     * @since 1.1.0
     */
    private function _isPositiveBelowMax($fAmount, $fMaxAmount)
    {
        return $fAmount > 0 && $fMaxAmount > 0 &&
            ((bcsub($fAmount, $fMaxAmount, Helper::BCSUB_SCALE) / $fMaxAmount) < Helper::FLOATING_POINT_EPSILON);
    }

    /**
     * Processes a request and returns a state array if it is valid.
     *
     * @param array $aRequestParameters
     * @return array|null
     *
     * @since 1.1.0
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
            $aState = $this->_handleRequestAction($sActionTitle, $sTransactionAmount);
        } catch (StandardException $oException) {
            $aState[self::KEY_MESSAGE] = $oException->getMessage();
            $aState[self::KEY_TYPE] = self::KEY_ERROR;
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
     * @since 1.1.0
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
     * @since 1.1.0
     */
    private function _filterPostProcessingActions($aPossibleOperations, $oPaymentMethod)
    {
        foreach ($aPossibleOperations as $sActionKey => $sDisplayValue) {
            $oTransaction = $oPaymentMethod->getPostProcessingTransaction($sActionKey);
            $oPayment = PaymentMethodHelper::getPaymentById(
                PaymentMethod::getOxidFromSDKName($oTransaction->getConfigKey())
            );

            if (!$oPayment->oxpayments__oxactive->value) {
                unset($aPossibleOperations[$sActionKey]);
            }
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
     * @since 1.1.0
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
     * @return array
     *
     * @since 1.1.0
     */
    private function _handleRequestAction($sActionTitle, $fAmount)
    {
        $oTransactionHandler = $this->_getTransactionHandler();
        $aResult = $oTransactionHandler->processAction($this->oTransaction, $sActionTitle, $fAmount);

        $bSuccess = $aResult[self::KEY_STATUS] === Transaction::STATE_SUCCESS;

        if (!$bSuccess) {
            return self::_getResultMessageArray(self::KEY_ERROR, $aResult[self::KEY_MESSAGE]);
        }

        return self::_getResultMessageArray(self::KEY_SUCCESS, Helper::translate('wd_text_generic_success'));
    }

    /**
     * Returns the result array containg a message type and text.
     *
     * @param string $sType    message type
     * @param string $sMessage message content
     *
     * @return array
     *
     * @since 1.1.0
     */
    private static function _getResultMessageArray($sType, $sMessage)
    {
        return [
            self::KEY_TYPE => $sType,
            self::KEY_MESSAGE => $sMessage,
        ];
    }

    /**
     * Returns an instance of TransactionHandler
     *
     * @return TransactionHandler
     *
     * @since 1.1.0
     */
    private function _getTransactionHandler()
    {
        // NOTE: if _oTransactionHandler got injected for testing, use it
        if ($this->_oTransactionHandler) {
            return $this->_oTransactionHandler;
        }

        $oConfig = $this->_getPaymentMethodConfig();

        return new TransactionHandler($this->_getBackendService($oConfig));
    }

    /**
     * Returns the payment method config for the currently selected transaction or null if none is set.
     *
     * @return Config | null
     *
     * @since 1.1.0
     */
    private function _getPaymentMethodConfig()
    {
        $oConfig = null;

        if (!is_null($this->oTransaction)) {
            $sPaymentId = $this->oTransaction->getPaymentType();
            $oPaymentMethod = PaymentMethodFactory::create($sPaymentId);
            $oConfig = $oPaymentMethod->getConfig();
        }

        return $oConfig;
    }
}
