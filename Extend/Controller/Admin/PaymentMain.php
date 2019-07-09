<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend\Controller\Admin;

use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\Oxid\Extend\Model\Payment;
use Wirecard\Oxid\Model\PayolutionBtwobPaymentMethod;
use Wirecard\Oxid\Model\PayolutionInvoicePaymentMethod;
use Wirecard\Oxid\Model\SepaDirectDebitPaymentMethod;
use Wirecard\Oxid\Model\SofortPaymentMethod;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\TransactionService;

/**
 * Controls the payment method config view.
 *
 * @mixin \OxidEsales\Eshop\Application\Controller\Admin\PaymentMain
 *
 * @since 1.0.0
 */
class PaymentMain extends PaymentMain_parent
{
    /**
     * @var TransactionService
     *
     * @since 1.1.0
     */
    private $_oTransactionService;

    /**
     * Stores the result of the save possible result.
     *
     * @since 1.2.0
     */
    private $_bIsSavePossible;

    /**
     * Tells if information about currency settings should be shown.
     * The information is shown any time merchant adds a currency which has empty configuration fields.
     *
     * @since 1.2.0
     */
    private $_bShowCurrencyHelp;

    /**
     * @inheritdoc
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function render()
    {
        $sParentReturn = parent::render();

        if ($this->_isCustomPaymentMethod()) {
            $sFnc = Registry::getRequest()->getRequestParameter('fnc');

            // for custom payment methods, additional validation logic needs to be
            // performed to check if only valid data was entered
            // if it is not valid, the 'save' or 'addfield' operation is aborted
            // and an error message shown in the frontend
            Helper::addToViewData($this, [
                'bConfigNotValid' => ($sFnc === 'save' || $sFnc === 'addfield') && !$this->_bIsSavePossible,
                'bShowCurrencyHelp' => $this->_bShowCurrencyHelp,
            ]);
        }

        return $sParentReturn;
    }

    /**
     * @inheritdoc
     *
     * @return void
     *
     * @throws \Http\Client\Exception
     *
     * @since 1.0.0
     */
    public function save()
    {
        // store is save possible result for use in render()
        $this->_bIsSavePossible = $this->_isSavePossible();
        if ($this->_isCustomPaymentMethod() && !$this->_bIsSavePossible) {
            // abort the save operation if the custom form validation failed
            return;
        }

        parent::save();
    }

    /**
     * Validates credentials, country code and creditor id
     *
     * @return bool
     *
     * @throws \Http\Client\Exception
     *
     * @since 1.0.0
     */
    private function _isSavePossible()
    {
        $aParams = Registry::getRequest()->getRequestParameter('editval');

        $bCredentialsValid = $this->_validateRequestParameters($aParams);

        return $bCredentialsValid && $this->_isCountryCodeValid($aParams) && $this->_isCreditorIdValid($aParams)
            && $this->_isPayolutionUrlSettingsValid($aParams);
    }

    /**
     * Checks if Payolution URL setting is valid.
     * If require consent is enabled, Payolution URL has to be set. Otherwise it can be empty.
     *
     * @param array $aParams
     *
     * @return bool
     *
     * @since 1.2.0
     */
    private function _isPayolutionUrlSettingsValid($aParams)
    {
        return !$this->_isPayolutionPaymentMethod($aParams['oxpayments__oxid'])
            || ($aParams['oxpayments__terms'] && strlen(trim($aParams['oxpayments__payolution_terms_url']))
                || !$aParams['oxpayments__terms']);
    }

    /**
     * Checks for a Payolution payment method (B2C or B2B)
     *
     * @param string $sPaymentId
     *
     * @return bool
     *
     * @since 1.3.0
     */
    private function _isPayolutionPaymentMethod($sPaymentId)
    {
        return $sPaymentId === PayolutionInvoicePaymentMethod::getName(true)
            || $sPaymentId === PayolutionBtwobPaymentMethod::getName(true);
    }

    /**
     * Checks if country code is valid
     *
     * @param array $aParams
     *
     * @return bool
     *
     * @since 1.1.0
     */
    private function _isCountryCodeValid($aParams)
    {
        // for Sofort it is required that a country code is settable
        // the country code must be of the format two characters, underscore, two characters: "en_gb"
        // this format is checked by the regular expression below
        $sCountryCode = $aParams['oxpayments__wdoxidee_countrycode'];
        return $aParams['oxpayments__oxid'] !== SofortPaymentMethod::getName(true)
            || (preg_match('/^[a-z]{2}_[a-z]{2}$/', $sCountryCode) === 1);
    }

    /**
     * Checks if creditor id is valid
     *
     * @param array $aParams
     *
     * @return bool
     *
     * @since 1.1.0
     */
    private function _isCreditorIdValid($aParams)
    {
        $sCreditorId = $aParams['oxpayments__wdoxidee_creditorid'];
        return $aParams['oxpayments__oxid'] !== SepaDirectDebitPaymentMethod::getName(true)
            || $this->_creditorIdValidation($sCreditorId);
    }

    /**
     * Validates creditor id
     *
     * @param string $sCreditorId
     *
     * @return bool
     *
     * @since 1.1.0
     */
    protected function _creditorIdValidation($sCreditorId)
    {
        //explanation for creditor id validation: https://www.iban.de/iban-pruefsumme.html

        $sCreditorId = strtolower(str_replace(' ', '', $sCreditorId));
        if (preg_match('/^[a-zA-Z]{2}[0-9]{2}[a-zA-Z0-9]{0,31}$/', $sCreditorId) !== 1 || strlen($sCreditorId) > 35) {
            return false;
        }

        //remove the default creditor business code
        $sCreditorId = str_replace('zzz', '', $sCreditorId);
        $sConvertedCreditorId = $this->_convertCreditorId($sCreditorId);

        $iModulo = bcmod($sConvertedCreditorId, '97');
        $sChecksum = substr($sCreditorId, 2, 2);
        if ($sChecksum < 10) { // removes 0 if number is <10 (09 => 9 ...)
            $sChecksum = substr($sChecksum, 1);
        }

        return (98 - $iModulo) === (int) $sChecksum;
    }

    /**
     * Converts creditor id
     *
     * @param string $sCreditorId
     *
     * @return string
     *
     * @since 1.1.0
     */
    private function _convertCreditorId($sCreditorId)
    {
        $aCharMapping = ['a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,'i'=>18,'j'=>19,'k'=>20,
            'l'=>21,'m'=>22,'n'=>23,'o'=>24,'p'=>25,'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,
            'x'=>33,'y'=>34,'z'=>35,];
        $sFormattedCreditorId = substr($sCreditorId, 4) . substr($sCreditorId, 0, 2) . '00';
        $aFormattedCreditorId = str_split($sFormattedCreditorId);
        $sConvertedCreditorId = "";

        foreach ($aFormattedCreditorId as $iIdx => $sValue) {
            if (!is_numeric($sValue)) {
                $aFormattedCreditorId[$iIdx] = $aCharMapping[$sValue];
            }
            $sConvertedCreditorId .= $aFormattedCreditorId[$iIdx];
        }

        return $sConvertedCreditorId;
    }

    /**
     * Checks if the credentials from the payment method form are valid
     *
     * @param array $aParams
     *
     * @return bool
     *
     * @throws \Http\Client\Exception
     *
     * @since 1.0.0
     */
    private function _validateRequestParameters($aParams)
    {
        $sUrl = $aParams['oxpayments__wdoxidee_apiurl'];
        $sUser = $aParams['oxpayments__wdoxidee_httpuser'];
        $sPass = $aParams['oxpayments__wdoxidee_httppass'];

        if ($sUrl && $sUser && $sPass) {
            $oConfig = new Config($sUrl, $sUser, $sPass);
            $oTransactionService = $this->_getTransactionService($oConfig);
            return $oTransactionService->checkCredentials();
        }

        // handle the case of different configuration per currency (currently only Payolution)
        // there it's only checked if config values are entered on the frontend but no validation is done on the backend
        return $this->_checkCurrencyConfigFields($aParams);
    }

    /**
     * Checks if the config fields for all selected allowed currencies have been set.
     *
     * @param array $aParams
     *
     * @return bool
     *
     * @since 1.2.0
     */
    private function _checkCurrencyConfigFields($aParams)
    {
        $aOldCurrencyValue = $this->_getPaymentMethod()->oxpayments__allowed_currencies->value ?? [];
        $aNewCurrencyValue = $aParams['oxpayments__allowed_currencies'];

        foreach ($aNewCurrencyValue as $sCurrency) {
            // it is only necessary to check this currency at this point if it was already saved before
            if (!in_array($sCurrency, $aOldCurrencyValue)) {
                $this->_bShowCurrencyHelp = true;
                continue;
            }

            if (!$this->_validateRequiredCurrencyFields($aParams, $sCurrency)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks that all the required fields for the selected currencies have been set.
     *
     * @param array  $aParams
     * @param string $sCurrency
     *
     * @return bool
     *
     * @since 1.2.0
     */
    private function _validateRequiredCurrencyFields($aParams, $sCurrency)
    {
        $aRequiredFields = [
            'oxpayments__httpuser_',
            'oxpayments__httppass_',
            'oxpayments__maid_',
            'oxpayments__secret_',
        ];

        foreach ($aRequiredFields as $sFieldNamePrefix) {
            $sFieldName = $sFieldNamePrefix . strtolower($sCurrency);

            if (empty($aParams[$sFieldName])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if a custom payment method is shown
     *
     * @return bool
     *
     * @since 1.0.0
     */
    private function _isCustomPaymentMethod()
    {
        return $this->_getPaymentMethod()->isCustomPaymentMethod();
    }

    /**
     * Returns the currently selected payment method.
     *
     * @return Payment
     *
     * @since 1.2.0
     */
    private function _getPaymentMethod()
    {
        $sOxId = $this->getEditObjectId();
        return PaymentMethodHelper::getPaymentById($sOxId);
    }

    /**
     * Used in tests to mock the transaction service
     *
     * @param TransactionService $oTransactionService
     *
     * @since 1.1.0
     */
    public function setTransactionService($oTransactionService)
    {
        $this->_oTransactionService = $oTransactionService;
    }

    /**
     *
     * @param Config $oConfig
     *
     * @return TransactionService
     *
     * @since 1.1.0
     */
    private function _getTransactionService($oConfig)
    {
        if (is_null($this->_oTransactionService)) {
            $this->_oTransactionService = new TransactionService($oConfig, Registry::getLogger());
        }

        return $this->_oTransactionService;
    }
}
