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

use Wirecard\Oxid\Core\PaymentMethodHelper;
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
            $this->setViewData('bConfigNotValid', ($sFnc === 'save' || $sFnc === 'addfield')
                && !$this->_isSavePossible());
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
     * Checks if it is possible to save the config
     *
     * @return bool
     *
     * @since 1.0.0
     */
    private function _isSavePossible()
    {
        $aParams = Registry::getRequest()->getRequestParameter('editval');

        $bCredentialsValid = $this->_validateRequestParameters($aParams);

        // for Sofort it is required that a country code is settable
        // the country code must be of the format two characters, underscore, two characters: "en_gb"
        // this format is checked by the regular expression below
        $sCountryCode = $aParams['oxpayments__wdoxidee_countrycode'];
        $bCountryCodeValid = $aParams['oxpayments__oxid'] !== SofortPaymentMethod::getName(true)
            || (preg_match('/^[a-z]{2}_[a-z]{2}$/', $sCountryCode) === 1);

        return $bCredentialsValid && $bCountryCodeValid;
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
