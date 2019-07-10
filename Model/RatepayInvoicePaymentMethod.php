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
 */
class RatepayInvoicePaymentMethod extends InvoicePaymentMethod
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
        $aMetaDataFields = [
            'allowed_currencies',
            'shipping_countries',
            'billing_countries',
            'billing_shipping',
        ];

        return array_merge(parent::getMetaDataFieldNames(), $aMetaDataFields);
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
        $oAccountHolder->setDateOfBirth(new DateTime(SessionHelper::getDbDateOfBirth(self::getName())));
        $oAccountHolder->setPhone(SessionHelper::getPhone(self::getName()));
        $oTransaction->setAccountHolder($oAccountHolder);
    }

    /**
     * @inheritdoc
     *
     * @return bool
     *
     * @since 1.2.0
     */
    protected function _isPhoneMandatory()
    {
        return true;
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

        $oPostProcBasket = new Basket();
        $oPostProcBasket->setVersion(RatepayInvoiceTransaction::class);

        $fAmount = $this->_addItemsToPostProcessingBasket($oParentTransaction, $aOrderItems, $oPostProcBasket);

        $oTransaction->setBasket($oPostProcBasket);
        $oTransaction->setAmount(new Amount(
            $fAmount,
            $oPostProcBasket->getTotalAmount()->getCurrency()
        ));
        return $oTransaction;
    }

    /**
     * @param \Wirecard\Oxid\Model\Transaction $oParentTransaction
     * @param array|null                       $aOrderItems
     * @param Basket                           $oPostProcBasket
     *
     * @return float the amount of the items in the basket
     *
     * @since 1.2.0
     */
    private function _addItemsToPostProcessingBasket($oParentTransaction, $aOrderItems, &$oPostProcBasket)
    {
        $oBasket = new Basket();
        $oBasket->parseFromXml(simplexml_load_string($oParentTransaction->getResponseXML()));

        $aItemsToAdd = self::_getItemsToAddToBasket($oBasket, $aOrderItems);

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

        return $fAmount;
    }

    /**
     * @param Basket $oBasket
     * @param array  $aOrderItems
     *
     * @return array
     *
     * @since 1.2.0
     */
    private static function _getItemsToAddToBasket($oBasket, $aOrderItems)
    {
        $aItemsToAdd = [];

        foreach ($aOrderItems as $sArticleNumber => $iQuantity) {
            if ($iQuantity < 1) {
                continue;
            }

            $oItem = self::_getRecalculatedItem($oBasket, $sArticleNumber, $iQuantity);
            if (!is_null($oItem)) {
                $aItemsToAdd[] = $oItem;
            }
        }

        return $aItemsToAdd;
    }

    /**
     * @param Basket $oBasket
     * @param string $sArticleNumber
     * @param int    $iQuantity
     *
     * @return Item|null
     *
     * @since 1.2.0
     */
    private static function _getRecalculatedItem($oBasket, $sArticleNumber, $iQuantity)
    {
        foreach ($oBasket as $iIndex => $oBasketItem) {

            /**
             * @var $oBasketItem Item
             */
            if ($oBasketItem->getArticleNumber() == $sArticleNumber) {
                $oBasketItem->setQuantity($iQuantity);
                return $oBasketItem;
            }
        }

        return null;
    }
}
