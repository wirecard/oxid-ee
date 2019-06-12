<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use DateTime;

use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Core\Exception\InputException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\Oxid\Core\SessionHelper;
use Wirecard\Oxid\Extend\Model\Order;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Item;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

/**
 * Payment method implementation for Ratepay Invoice
 *
 * @since 1.2.0
 *
 * @codingStandardsIgnoreStart Custom.Classes.ClassLinesOfCode.MaxExceeded
 * Will be fixed with https://github.com/wirecard/oxid-ee/pull/132
 *
 */
class RatepayInvoicePaymentMethod extends PaymentMethod
{

    const UNIQUE_TOKEN_VARIABLE = 'wd_ratepay_unique_token';

    /**
     * @inheritdoc
     *
     * @since 1.2.0
     */
    protected static $_sName = "ratepay-invoice";

    /**
     * @inheritdoc
     *
     * @return Config
     *
     * @since 1.2.0
     */
    public function getConfig()
    {
        $oConfig = parent::getConfig();

        $oPaymentMethodConfig = new PaymentMethodConfig(
            RatepayInvoiceTransaction::NAME,
            $this->_oPayment->oxpayments__wdoxidee_maid->value,
            $this->_oPayment->oxpayments__wdoxidee_secret->value
        );

        $oConfig->add($oPaymentMethodConfig);
        return $oConfig;
    }

    /**
     * @inheritdoc
     *
     * @return RatepayInvoiceTransaction
     *
     * @since 1.2.0
     */
    public function getTransaction()
    {
        return new RatepayInvoiceTransaction();
    }

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.2.0
     */
    public function getConfigFields()
    {
        $aAdditionalFields = [
            'descriptor' => [
                'type' => 'select',
                'field' => 'oxpayments__wdoxidee_descriptor',
                'options' => [
                    '1' => Helper::translate('wd_yes'),
                    '0' => Helper::translate('wd_no'),
                ],
                'title' => Helper::translate('wd_config_descriptor'),
                'description' => Helper::translate('wd_config_descriptor_desc'),
            ],
            'additionalInfo' => [
                'type' => 'select',
                'field' => 'oxpayments__wdoxidee_additional_info',
                'options' => [
                    '1' => Helper::translate('wd_yes'),
                    '0' => Helper::translate('wd_no'),
                ],
                'title' => Helper::translate('wd_config_additional_info'),
                'description' => Helper::translate('wd_config_additional_info_desc'),
            ],
            'deleteCanceledOrder' => [
                'type' => 'select',
                'field' => 'oxpayments__wdoxidee_delete_canceled_order',
                'options' => [
                    '1' => Helper::translate('wd_yes'),
                    '0' => Helper::translate('wd_no'),
                ],
                'title' => Helper::translate('wd_config_delete_cancel_order'),
                'description' => Helper::translate('wd_config_delete_cancel_order_desc'),
            ],
            'deleteFailedOrder' => [
                'type' => 'select',
                'field' => 'oxpayments__wdoxidee_delete_failed_order',
                'options' => [
                    '1' => Helper::translate('wd_yes'),
                    '0' => Helper::translate('wd_no'),
                ],
                'title' => Helper::translate('wd_config_delete_failure_order'),
                'description' => Helper::translate('wd_config_delete_failure_order_desc'),
            ],
            'allowedCurrencies' => [
                'type' => 'multiselect',
                'field' => 'oxpayments__allowed_currencies',
                'options' => PaymentMethodHelper::getCurrencyOptions(),
                'title' => Helper::translate('wd_config_allowed_currencies'),
                'description' => Helper::translate('wd_config_allowed_currencies_desc'),
                'required' => true,
            ],
            'shippingCountries' => [
                'type' => 'multiselect',
                'field' => 'oxpayments__shipping_countries',
                'options' => PaymentMethodHelper::getCountryOptions(),
                'title' => Helper::translate('wd_config_shipping_countries'),
                'description' => Helper::translate('wd_config_shipping_countries_desc'),
                'required' => true,
            ],
            'billingCountries' => [
                'type' => 'multiselect',
                'field' => 'oxpayments__billing_countries',
                'options' => PaymentMethodHelper::getCountryOptions(),
                'title' => Helper::translate('wd_config_billing_countries'),
                'description' => Helper::translate('wd_config_billing_countries_desc'),
                'required' => true,
            ],
            'billingShipping' => [
                'type' => 'select',
                'field' => 'oxpayments__billing_shipping',
                'options' => [
                    '1' => Helper::translate('wd_yes'),
                    '0' => Helper::translate('wd_no'),
                ],
                'title' => Helper::translate('wd_config_billing_shipping'),
                'description' => Helper::translate('wd_config_billing_shipping_desc'),
            ],
        ];

        return parent::getConfigFields() + $aAdditionalFields;
    }

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.2.0
     */
    public function getPublicFieldNames()
    {
        return array_merge(
            parent::getPublicFieldNames(),
            [
                'descriptor',
                'additionalInfo',
                'deleteCanceledOrder',
                'deleteFailedOrder',
                'allowedCurrencies',
                'shippingCountries',
                'billingCountries',
                'billingShipping',
            ]
        );
    }

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.2.0
     */
    public function getMetaDataFieldNames()
    {
        return [
            'allowed_currencies',
            'shipping_countries',
            'billing_countries',
            'billing_shipping',
        ];
    }

    /**
     * @inheritdoc
     *
     * @param RatepayInvoiceTransaction $oTransaction
     * @param Order                     $oOrder
     *
     * @throws \Exception
     *
     * @since 1.2.0
     */
    public function addMandatoryTransactionData(&$oTransaction, $oOrder)
    {
        $oSession = Registry::getSession();
        $oBasket = $oSession->getBasket();
        $oWdBasket = $oBasket->createTransactionBasket();

        $oTransaction->setBasket($oWdBasket);
        $oTransaction->setShipping($oOrder->getShippingAccountHolder());
        $oTransaction->setOrderNumber($oOrder->oxorder__oxid->value);

        $oAccountHolder = $oOrder->getAccountHolder();
        $oAccountHolder->setDateOfBirth(new DateTime(SessionHelper::getDbDateOfBirth()));
        $oAccountHolder->setPhone(SessionHelper::getPhone());
        $oTransaction->setAccountHolder($oAccountHolder);
    }

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.2.0
     */
    public function getCheckoutFields()
    {
        $aCheckoutFields = null;

        $aCheckoutFields = [
            'dateOfBirth' => [
                'type' => $this->_getCheckoutFieldType(SessionHelper::isDateOfBirthSet()),
                'title' => Helper::translate('wd_birthdate_input'),
                'description' => Helper::translate('wd_date_format_user_hint'),
                'required' => true,
            ],
        ];

        $aCheckoutFields = array_merge($aCheckoutFields, [
            'phone' => [
                'type' => $this->_getCheckoutFieldType(SessionHelper::isPhoneValid()),
                'title' => Helper::translate('wd_phone'),
                'required' => true,
            ],
        ]);

        if ($this->_checkSaveCheckoutFields($aCheckoutFields)) {
            $aCheckoutFields = array_merge($aCheckoutFields, [
                'saveCheckoutFields' => [
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
    private function _checkSaveCheckoutFields($aCheckoutFields)
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
    private function _getCheckoutFieldType($bIsValid)
    {
        return $bIsValid ? 'hidden' : 'text';
    }

    /**
     * @inheritdoc
     *
     * @since 1.2.0
     */
    public function onBeforeOrderCreation()
    {
        $this->checkPayStepUserInput();
    }

    /**
     * Checks the user data if mandatory fields are set correctly for guaranteed invoice and saves them if needed
     *
     * @since 1.2.0
     */
    public function checkPayStepUserInput()
    {
        $oUser = Registry::getSession()->getUser();

        if (SessionHelper::isDateOfBirthSet()) {
            $oUser->oxuser__oxbirthdate = new Field(SessionHelper::getDbDateOfBirth());
        }

        if (SessionHelper::isPhoneValid()) {
            $oUser->oxuser__oxfon = new Field(SessionHelper::getPhone());
        }

        if (SessionHelper::getSaveCheckoutFields() === '1') {
            $oUser->save();
        }

        $this->_validateUserInput();
    }

    /**
     * Validates the user input and throws a specific error if an input is wrong
     *
     * @since 1.2.0
     */
    private function _validateUserInput()
    {
        if (!SessionHelper::isUserOlderThan(18)) {
            throw new InputException(Helper::translate('wd_ratepayinvoice_fields_error'));
        }

        if (!SessionHelper::isPhoneValid()) {
            throw new InputException(Helper::translate('wd_text_generic_error'));
        }
    }

    /**
     * Checks if the user is older than 18 or the date of birth needs to be entered
     *
     * @return bool
     *
     * @since 1.2.0
     */
    private function _checkDateOfBirth()
    {
        return !SessionHelper::isDateOfBirthSet() || SessionHelper::isUserOlderThan(18);
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
     *
     * @inheritdoc
     *
     * @param string                           $sAction
     * @param \Wirecard\Oxid\Model\Transaction $oParentTransaction
     * @param array|null                       $aOrderItems
     *
     * @return Transaction
     *
     * @since 1.2.0
     */
    public function getPostProcessingTransaction($sAction, $oParentTransaction, $aOrderItems = null)
    {
        $oTransaction = new RatepayInvoiceTransaction();
        $aBasketResult = $this->_createPostProcessingBasket($oParentTransaction, $aOrderItems);

        /**
         * @var $oBasket Basket
         */
        $oBasket = $aBasketResult[0];
        $oTransaction->setBasket($oBasket);
        $oTransaction->setAmount(new Amount(
            $aBasketResult[1],
            $oBasket->getTotalAmount()->getCurrency()
        ));
        return $oTransaction;
    }

    /**
     * @param \Wirecard\Oxid\Model\Transaction $oParentTransaction
     * @param array|null                       $aOrderItems
     *
     * @return array
     *
     * @since 1.2.0
     */
    private function _createPostProcessingBasket($oParentTransaction, $aOrderItems)
    {
        $oBasket = new Basket();

        $oXmlBasket = simplexml_load_string($oParentTransaction->getResponseXML());
        $oBasket->parseFromXml($oXmlBasket);

        $aItemsToAdd = self::_getItemsToAddToBasket($oBasket, $oXmlBasket, $aOrderItems);

        $oPostProcBasket = new Basket();
        $oPostProcBasket->setVersion(RatepayInvoiceTransaction::class);

        $iRoundPrecision = Helper::getCurrencyRoundPrecision($oBasket->getTotalAmount()->getCurrency());

        $fAmount = 0;
        foreach ($aItemsToAdd as $oBasketItem) {
            /**
             * @var $oBasketItem Item
             */
            $oPostProcBasket->add($oBasketItem);
            $fAmount = round(
                bcadd(
                    $fAmount,
                    $oBasketItem->getPrice()->getValue() * $oBasketItem->getQuantity(),
                    Helper::BCSUB_SCALE
                ),
                $iRoundPrecision
            );
        }

        return [$oPostProcBasket, $fAmount];
    }

    /**
     * @param Basket            $oBasket
     * @param \SimpleXMLElement $oXmlBasket
     * @param array             $aOrderItems
     *
     * @return array
     *
     * @since 1.2.0
     */
    private static function _getItemsToAddToBasket($oBasket, $oXmlBasket, $aOrderItems)
    {
        $aItemsToAdd = [];

        foreach ($aOrderItems as $sArticleNumber => $iQuantity) {
            if ($iQuantity < 1) {
                continue;
            }

            $oItem = self::_getRecalculatedItem($oBasket, $oXmlBasket, $sArticleNumber, $iQuantity);
            if (!is_null($oItem)) {
                $aItemsToAdd[] = $oItem;
            }
        }

        return $aItemsToAdd;
    }

    /**
     * @param Basket            $oBasket
     * @param \SimpleXMLElement $oXmlBasket
     * @param string            $sArticleNumber
     * @param int               $iQuantity
     *
     * @return Item|null
     *
     * @since 1.2.0
     */
    private static function _getRecalculatedItem($oBasket, $oXmlBasket, $sArticleNumber, $iQuantity)
    {
        foreach ($oBasket as $iIndex => $oBasketItem) {

            /**
             * @var $oBasketItem Item
             */
            if ($oBasketItem->getArticleNumber() == $sArticleNumber) {
                $oBasketItem->setQuantity($iQuantity);

                //set Tax-rate ourselves. paymentSdk xml parser does not do that
                $fTaxRate = (float) $oXmlBasket->{'order-items'}->children()[$iIndex]->{'tax-rate'};
                $oBasketItem->setTaxRate($fTaxRate);

                return $oBasketItem;
            }
        }

        return null;
    }
}
// @codingStandardsIgnoreEnd
