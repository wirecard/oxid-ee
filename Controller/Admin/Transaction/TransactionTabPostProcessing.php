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

use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\PaymentMethodFactory;
use Wirecard\Oxid\Core\PostProcessingHelper;
use Wirecard\Oxid\Core\TransactionHandler;
use Wirecard\Oxid\Model\PaymentMethod\PaymentOnInvoicePaymentMethod;
use Wirecard\Oxid\Model\Transaction;

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
    const KEY_ORDER_ITEMS = 'order_items';

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
            $this->_oTransaction->load($this->_sListObjectId);
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
     * @throws \Exception
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
        if ($this->_oTransaction->wdoxidee_ordertransactions__state->value !== Transaction::STATE_CLOSED) {
            $this->_aActions = $this->_getPostProcessingActions();
        }

        $aRequestParameters = $this->_getRequestParameters();
        $sTransactionId = $this->_oTransaction->wdoxidee_ordertransactions__transactionid->value;

        //setting the relevant data to render the tab
        Helper::addToViewData($this, [
            'actions' => $this->_aActions,
            'maxAmount' => $this->_getTransactionHandler()->getTransactionMaxAmount($sTransactionId),
            'message' => $this->_processRequest($aRequestParameters),
            'currency' => $this->_oTransaction->wdoxidee_ordertransactions__currency->value,
            'emptyText' => Helper::translate('wd_text_no_further_operations_possible'),
        ]);

        if (PostProcessingHelper::shouldUseOrderItems($this->_oTransaction)) {
            $this->_addOrderItemsToViewData();
        }

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
        $oRequest = Registry::getRequest();
        $aActionConfig = null;

        foreach ($this->_aActions as $sActionType => $aSingleActionConfig) {
            if ($oRequest->getRequestParameter($sActionType)) {
                $aActionConfig = $aSingleActionConfig;
            }
        }

        //parsing the parameters from the request url
        return [
            self::KEY_AMOUNT => Helper::getFloatFromString(
                $oRequest->getRequestParameter(self::KEY_AMOUNT) ?? ''
            ),
            self::KEY_ACTION => $aActionConfig,
            self::KEY_ORDER_ITEMS => array_combine(
                $oRequest->getRequestParameter('article-number') ?? [],
                $oRequest->getRequestParameter('quantity') ?? []
            ),
        ];
    }

    /**
     * Validates a request.
     *
     * @param array $aRequestParameters
     *
     * @return void
     *
     * @throws StandardException
     *
     * @since 1.1.0
     */
    private function _validateRequest($aRequestParameters)
    {
        $aOrderItems = $aRequestParameters[self::KEY_ORDER_ITEMS];

        if ($aOrderItems) {
            $this->_validateOrderItems($aOrderItems);
            //if order items validation is okay, don't check amount.
            return;
        }

        $fAmount = $aRequestParameters[self::KEY_AMOUNT];
        $this->_validateAmount($fAmount);
    }

    /**
     * Check for correct amount
     *
     * @param float $fAmount
     *
     * @throws StandardException
     *
     * @since 1.2.0
     */
    private function _validateAmount($fAmount)
    {
        if (!$this->_isAmountNumeric($fAmount)) {
            throw new StandardException(Helper::translate('wd_text_generic_error'));
        }

        $sTransactionId = $this->_oTransaction->wdoxidee_ordertransactions__transactionid->value;
        $fMaxAmount = $this->_getTransactionHandler()->getTransactionMaxAmount($sTransactionId);

        if (!Helper::isPositiveBelowMax($fAmount, $fMaxAmount)) {
            throw new StandardException(Helper::translate('wd_total_amount_not_in_range_text'));
        }
    }

    /**
     *
     * Checks order items quantity
     *
     * @param array $aOrderItems
     *
     * @throws StandardException if all order items are zero
     *
     * @return void
     *
     * @since 1.2.0
     */
    private function _validateOrderItems($aOrderItems)
    {
        foreach ($aOrderItems as $sArticleNumber => $iQuantity) {
            // item are valid as soon as there is one item with a quantity
            if ($iQuantity > 0) {
                return;
            }
        }

        throw new StandardException(Helper::translate('wd_text_generic_error'));
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
     * Processes a request and returns a state array if it is valid.
     *
     * @param array $aRequestParameters
     *
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
            $aOrderItems = $aRequestParameters[self::KEY_ORDER_ITEMS];

            // execute the callback method defined in the "action" request parameter
            $aState = $this->_handleRequestAction($sActionTitle, $sTransactionAmount, $aOrderItems);
        } catch (Exception $oException) {
            $aState[self::KEY_MESSAGE] = $oException->getMessage();
            $aState[self::KEY_TYPE] = self::KEY_ERROR;
            $this->_oLogger->error($oException->getMessage(), [$oException]);
        }

        return $aState;
    }

    /**
     * Returns an array of processing actions to be displayed below the amount input field.
     *
     * @throws \Exception in case the payment method type is not found
     *
     * @return array
     *
     * @since 1.1.0
     */
    protected function _getPostProcessingActions()
    {
        $sPaymentId = $this->_oTransaction->getPaymentType();

        // Need to create a transaction object with the ID of the currently selected one to get
        // the available post-processing operations from the Payment SDK
        $oPaymentMethod = PaymentMethodFactory::create($sPaymentId);
        $oTransaction = $oPaymentMethod->getTransaction();
        $sParentTransactionId = $this->_oTransaction->wdoxidee_ordertransactions__transactionid->value;
        $oTransaction->setParentTransactionId($sParentTransactionId);

        $oConfig = $this->_getPaymentMethodConfig();
        $aPossibleOperations = $this->_getBackendService($oConfig)->retrieveBackendOperations($oTransaction, true);

        if ($aPossibleOperations === false || count($aPossibleOperations) <= 0) {
            return [];
        }

        $aPossibleOperations = PostProcessingHelper::filterPostProcessingActions(
            $aPossibleOperations,
            $oPaymentMethod,
            $this->_oTransaction
        );

        return $this->_getTranslatedPostProcessingActions($aPossibleOperations);
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
     * @param string     $sActionTitle
     * @param float|null $fAmount
     * @param array|null $aOrderItems
     *
     * @return array
     *
     * @throws StandardException
     * @throws \Exception
     *
     * @since 1.1.0
     */
    private function _handleRequestAction($sActionTitle, $fAmount, $aOrderItems)
    {
        $oTransactionHandler = $this->_getTransactionHandler();
        $aResult = $oTransactionHandler->processAction($this->_oTransaction, $sActionTitle, $fAmount, $aOrderItems);

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
     * Returns an instance of TransactionHandler (singleton)
     *
     * @return TransactionHandler
     *
     * @throws StandardException
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
     * @throws StandardException
     *
     * @since 1.1.0
     */
    private function _getPaymentMethodConfig()
    {
        $oConfig = null;
        if (!is_null($this->_oTransaction)) {
            $sOrderPaymentId = $this->_oTransaction->getPaymentType();

            if (!$sOrderPaymentId && $this->_oTransaction->isPoiPiaPaymentMethod()) {
                // Since POI and PIA payment methods share configuration, we create POI config.
                $oPaymentMethod = PaymentMethodFactory::create(PaymentOnInvoicePaymentMethod::getName());
                $oConfig = $oPaymentMethod->getConfig();
                return $oConfig;
            }

            $oPaymentMethod = PaymentMethodFactory::create($sOrderPaymentId);
            $oConfig = $oPaymentMethod->getConfig();
        }

        return $oConfig;
    }

    /**
     * Adds the order items to the view data
     *
     * @throws StandardException
     *
     * @since 1.2.0
     */
    private function _addOrderItemsToViewData()
    {
        Helper::addToViewData($this, [
            'data' => [
                "head" => [
                    ['text' => Helper::translate('wd_text_article_number')],
                    ['text' => Helper::translate('wd_text_article_name')],
                    ['text' => Helper::translate('wd_amount')],
                    ['text' => Helper::translate('wd_text_quantity')],
                ],
                "body" => PostProcessingHelper::getMappedTableOrderItems($this->_oTransaction),
            ],
        ]);
    }
}
