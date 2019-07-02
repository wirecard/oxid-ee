<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Core\Exception\InputException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\SessionHelper;

/**
 * Payment method implementation for Invoice payments
 *
 * @since 1.2.0
 */
abstract class InvoicePaymentMethod extends PaymentMethod
{
    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.2.0
     */
    public function getCheckoutFields()
    {
        $aCheckoutFields = [
            'dateOfBirth' . self::getName() => [
                'type' => $this->_getCheckoutFieldType(SessionHelper::isDateOfBirthSet(self::getName())),
                'title' => Helper::translate('wd_birthdate_input'),
                'description' => Helper::translate('wd_date_format_user_hint'),
                'required' => true,
            ],
        ];

        if ($this->_isPhoneMandatory()) {
            $aCheckoutFields = array_merge($aCheckoutFields, [
                'phone' . self::getName() => [
                    'type' => $this->_getCheckoutFieldType(SessionHelper::isPhoneValid(self::getName())),
                    'title' => Helper::translate('wd_phone'),
                    'required' => true,
                ],
            ]);
        }

        if ($this->_checkSaveCheckoutFields($aCheckoutFields)) {
            $aCheckoutFields = array_merge($aCheckoutFields, [
                'saveCheckoutFields' . self::getName() => [
                    'type' => 'select',
                    'options' => [
                        '1' => Helper::translate('wd_yes'),
                        '0' => Helper::translate('wd_no'),
                    ],
                    'title' => Helper::translate('wd_save_to_user_account'),
                ],
            ]);
        }

        return $aCheckoutFields;
    }

    /**
     * @inheritdoc
     *
     * @return bool
     *
     * @throws \Exception
     *
     * @since 1.2.0
     */
    public function isPaymentPossible()
    {
        $oSession = Registry::getSession();
        $oBasket = $oSession->getBasket();

        // if basket amount is within range is checked by oxid, no need to handle that
        return $this->_checkDateOfBirth() &&
            $this->_areArticlesAllowed($oBasket->getBasketArticles(), $oBasket->getVouchers()) &&
            $this->_isCurrencyAllowed($oBasket->getBasketCurrency()) &&
            $this->_areAddressesAllowed(SessionHelper::getBillingCountryId(), SessionHelper::getShippingCountryId());
    }

    /**
     * Returns true if the save checkout fields selection option should be shown (fields are shown, user is logged in)
     *
     * @param array $aCheckoutFields
     *
     * @return bool
     *
     * @since 1.2.0
     */
    protected function _checkSaveCheckoutFields($aCheckoutFields)
    {
        $bDataToSave = false;

        foreach ($aCheckoutFields as $aCheckoutField) {
            if ($aCheckoutField['type'] !== 'hidden') {
                $bDataToSave = true;
            }
        }

        return $bDataToSave && Registry::getSession()->getUser()->oxuser__oxpassword->value !== '';
    }

    /**
     * Returns 'hidden' if the field value is already valid, 'text' otherwise
     *
     * @param bool $bIsValid
     *
     * @return string
     *
     * @since 1.2.0
     */
    protected function _getCheckoutFieldType($bIsValid)
    {
        return $bIsValid ? 'hidden' : 'text';
    }

    /**
     * Checks if the user is older than 18 or the date of birth needs to be entered
     *
     * @return bool
     *
     * @throws \Exception
     *
     * @since 1.2.0
     */
    protected function _checkDateOfBirth()
    {
        return !SessionHelper::isDateOfBirthSet(self::getName()) || SessionHelper::isUserOlderThan(18, self::getName());
    }

    /**
     * Checks if given articles are allowed for this payment.
     *
     * @param array $aArticles
     * @param array $aVouchers
     *
     * @return bool
     *
     * @since 1.2.0
     */
    private function _areArticlesAllowed($aArticles, $aVouchers = [])
    {
        if ($aVouchers) {
            return false;
        }

        foreach ($aArticles as $oArticle) {
            if ($oArticle->oxarticles__oxisdownloadable->value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if the selected currency is allowed for this payment.
     *
     * @param object $oCurrency
     *
     * @return bool
     *
     * @since 1.2.0
     */
    private function _isCurrencyAllowed($oCurrency)
    {
        $oPayment = $this->getPayment();

        return in_array($oCurrency->name, $oPayment->oxpayments__allowed_currencies->value ?? []);
    }

    /**
     * Checks if given billing and shipping countries are allowed for this payment.
     *
     * @param string      $sBillingCountryId
     * @param string|null $sShippingCountryId
     *
     * @return bool
     *
     * @since 1.2.0
     */
    private function _areAddressesAllowed($sBillingCountryId, $sShippingCountryId)
    {
        $oPayment = $this->getPayment();
        $oBillingCountry = oxNew(Country::class);
        $oShippingCountry = oxNew(Country::class);

        $oBillingCountry->load($sBillingCountryId);
        $oShippingCountry->load($sShippingCountryId ?? $sBillingCountryId);

        return in_array(
            $oBillingCountry->oxcountry__oxisoalpha2->value,
            $oPayment->oxpayments__billing_countries->value ?? []
        ) && in_array(
            $oShippingCountry->oxcountry__oxisoalpha2->value,
            $oPayment->oxpayments__shipping_countries->value ?? []
        ) && (
            !$oPayment->oxpayments__billing_shipping->value ||
            !$sShippingCountryId
        );
    }

    /**
     * @inheritdoc
     *
     * @throws InputException
     *
     * @since 1.2.0
     */
    public function onBeforeOrderCreation()
    {
        $this->_checkPayStepUserInput();
    }

    /**
     * Checks the user data if mandatory fields are set correctly for guaranteed invoice and saves them if needed
     *
     * @throws InputException
     *
     * @since 1.2.0
     */
    private function _checkPayStepUserInput()
    {
        $oUser = Registry::getSession()->getUser();

        if (SessionHelper::isDateOfBirthSet(self::getName())) {
            $oUser->oxuser__oxbirthdate = new Field(SessionHelper::getDbDateOfBirth(self::getName()));
        }

        if (SessionHelper::isPhoneValid(self::getName())) {
            $oUser->oxuser__oxfon = new Field(SessionHelper::getPhone(self::getName()));
        }

        if (SessionHelper::getSaveCheckoutFields(self::getName()) === '1') {
            $oUser->save();
        }

        $this->_validateUserInput();
    }

    /**
     * Validates the user input and throws a specific error if an input is wrong
     *
     * @throws InputException
     * @throws \Exception
     *
     * @since 1.2.0
     */
    protected function _validateUserInput()
    {
        if (!SessionHelper::isUserOlderThan(18, self::getName())) {
            throw new InputException(Helper::translate('wd_ratepayinvoice_fields_error'));
        }

        if ($this->_isPhoneMandatory() && !SessionHelper::isPhoneValid(self::getName())) {
            throw new InputException(Helper::translate('wd_text_generic_error'));
        }
    }

    /**
     * Returns true if phone number is a mandatory input field
     *
     * @since 1.2.0
     */
    abstract protected function _isPhoneMandatory();
}
