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
     * @inheritdoc
     *
     * @since 1.0.0
     */
    protected $_sThisTemplate = 'tab_post_processing.tpl';

    /**
     * TransactionTab constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->oTransaction = oxNew(Transaction::class);

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
    public function render(): string
    {
        $sTemplate = parent::render();
        $aRequestParameters = $this->_getRequestParameters();

        $this->_aViewData += [
            'actions' => $this->_getPostProcessingActions(),
            'requestParameters' => $aRequestParameters,
            'alert' => $this->_processRequest($aRequestParameters),
            'currency' => $this->oTransaction->wdoxidee_ordertransactions__currency->value,
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

        foreach ($this->_getPostProcessingActions() as $sActionType => $aSingleActionConfig) {
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

        if (!$fAmount || !is_numeric($fAmount)) {
            throw new Exception(Helper::translate('text_generic_error'));
        }

        if ($fAmount < 0 || $fAmount > $this->oTransaction->wdoxidee_ordertransactions__amount->value) {
            throw new Exception(Helper::translate('total_amount_not_in_range_text'));
        }
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

            // execute the callback method defined in the "action" request parameter
            $aState['message'] = $this->{$aRequestParameters['action']['callback']}($aRequestParameters['amount']);
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
     * @return array
     *
     * @since 1.0.0
     */
    protected function _getPostProcessingActions(): array
    {
        // TODO return only supported actions for the payment
        return [
            'cancel' => [
                'title' => Helper::translate('cancel'),
                'callback' => '_onCancel',
            ],
            'capture' => [
                'title' => Helper::translate('pay'),
                'callback' => '_onCapture',
            ],
            'refund' => [
                'title' => Helper::translate('refund'),
                'callback' => '_onRefund',
            ],
        ];
    }

    /**
     * Callback function for the 'cancel' action.
     *
     * @param float $fAmount
     * @return string
     *
     * @since 1.0.0
     */
    private function _onCancel(float $fAmount): string
    {
        // TODO
        return Helper::translate('text_generic_success');
    }

    /**
     * Callback function for the 'capture' action.
     *
     * @param float $fAmount
     * @return string
     *
     * @since 1.0.0
     */
    private function _onCapture(float $fAmount): string
    {
        // TODO
        return Helper::translate('text_generic_success');
    }

    /**
     * Callback function for the 'refund' action.
     *
     * @param float $fAmount
     * @return string
     *
     * @since 1.0.0
     */
    private function _onRefund(float $fAmount): string
    {
        // TODO
        return Helper::translate('text_generic_success');
    }
}