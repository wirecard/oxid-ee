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
use Wirecard\Oxid\Model\SofortPaymentMethod;
use Wirecard\Oxid\Model\SepaDirectDebitPaymentMethod;

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
                'bConfigNotValid' => ($sFnc === 'save' || $sFnc === 'addfield') && !$this->_isSavePossible(),
            ]);
        }

        return $sParentReturn;
    }

    /**
     * @inheritdoc
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function save()
    {
        if ($this->_isCustomPaymentMethod() && !$this->_isSavePossible()) {
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
     * @since 1.0.0
     */
    private function _isSavePossible()
    {
        $aParams = Registry::getRequest()->getRequestParameter('editval');

        $bCredentialsValid = $this->_validateRequestParameters($aParams);

        return $bCredentialsValid && $this->_isCountryCodeValid($aParams) && $this->_isCreditorIdValid($aParams);
    }

    /**
     * Checks if country code is valid
     *
     * @param array $aParams
     *
     * @return bool
     *
     * @since 1.0.1
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
     * @since 1.0.1
     */
    private function _isCreditorIdValid($aParams)
    {
        $sCreditorId = $aParams['oxpayments__wdoxidee_creditorid'];
        return $aParams['oxpayments__oxid'] !== SepaDirectDebitPaymentMethod::getName(true)
            || $this->_creditorIdValidation($sCreditorId);
    }



    /**
     * Checks if it is possible to save the config
     *
     * @param bool $bCredentialsValid
     * @param bool $bCountryCodeValid
     * @param bool $bCreditorIdValid
     *
     * @return bool
     *
     * @since 1.0.1
     */
    private function _isInputValid($bCredentialsValid, $bCountryCodeValid, $bCreditorIdValid)
    {
        return $bCredentialsValid && $bCountryCodeValid && $bCreditorIdValid;
    }

    /**
     * Validates creditor id
     *
     * @param string $sCreditorId
     *
     * @return bool
     *
     * @since 1.0.1
     */
    private function _creditorIdValidation($sCreditorId)
    {
        //explanation for creditor id validation: https://www.iban.de/iban-pruefsumme.html

        $sCreditorId =  strtolower(str_replace(' ', '', $sCreditorId));
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
     * @since 1.0.1
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
     * @since 1.0.0
     */
    private function _validateRequestParameters($aParams)
    {
        $sUrl = $aParams['oxpayments__wdoxidee_apiurl'];
        $sUser = $aParams['oxpayments__wdoxidee_httpuser'];
        $sPass = $aParams['oxpayments__wdoxidee_httppass'];

        if ($sUrl && $sUser && $sPass) {
            $oConfig = new Config($sUrl, $sUser, $sPass);
            $oTransactionService = new TransactionService($oConfig, Registry::getLogger());
            return $oTransactionService->checkCredentials();
        }

        return false;
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
        $sOxId = $this->getEditObjectId();
        $oPayment = PaymentMethodHelper::getPaymentById($sOxId);
        return $oPayment->isCustomPaymentMethod();
    }
}
