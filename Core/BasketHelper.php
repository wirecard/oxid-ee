<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\BasketItem;
use OxidEsales\Eshop\Application\Model\Voucher;
use OxidEsales\Eshop\Core\Exception\ArticleInputException;
use OxidEsales\Eshop\Core\Exception\NoArticleException;
use OxidEsales\Eshop\Core\Registry;

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Basket as WdBasket;
use Wirecard\PaymentSdk\Entity\Item;

/**
 * Helper class to handle Basket Items
 *
 * @since 1.0.0
 */
class BasketHelper
{
    /**
     * Adds all payment costs to the basket
     *
     * @param WdBasket $oTransactionBasket the paymentSDK basket item
     * @param Basket   $oBasket            the OXID basket item
     * @param string   $sCurrency          the currency name
     *
     * @since 1.0.0
     */
    public static function addPaymentCostsToBasket(&$oTransactionBasket, $oBasket, $sCurrency)
    {
        $oPaymentCost = $oBasket->getPaymentCost();

        if ($oPaymentCost && !empty($oPaymentCost->getPrice())) {
            $sName = Helper::translate('wdpg_payment_cost');
            $oItem = self::_createItem(
                $sName,
                $oPaymentCost->getBruttoPrice(),
                $sCurrency,
                1,
                $sName,
                $sName,
                $oPaymentCost->getVat(),
                $oPaymentCost->getVatValue()
            );

            $oTransactionBasket->add($oItem);
        }
    }

    /**
     * Adds all gift card costs to the basket
     *
     * @param WdBasket $oWdBasket the paymentSDK basket item
     * @param Basket   $oBasket   the OXID basket item
     * @param string   $sCurrency
     *
     * @since 1.0.0
     */
    public static function addGiftCardCostsToBasket(&$oWdBasket, $oBasket, $sCurrency)
    {
        $oGiftCardCost = $oBasket->getGiftCardCost();

        if ($oGiftCardCost && !empty($oGiftCardCost->getPrice())) {
            $sName = Helper::translate('GREETING_CARD');
            $oItem = self::_createItem(
                $sName,
                $oGiftCardCost->getBruttoPrice(),
                $sCurrency,
                1,
                $sName,
                $sName,
                $oGiftCardCost->getVat(),
                $oGiftCardCost->getVatValue()
            );

            $oWdBasket->add($oItem);
        }
    }

    /**
     * Adds all wrapping costs to the basket
     *
     * @param WdBasket $oWdBasket the paymentSDK basket item
     * @param Basket   $oBasket   the OXID basket item
     * @param string   $sCurrency
     *
     * @since 1.0.0
     */
    public static function addWrappingCostsToBasket(&$oWdBasket, $oBasket, $sCurrency)
    {
        $oWrappingCost = $oBasket->getWrappingCost();

        if ($oWrappingCost && !empty($oWrappingCost->getPrice())) {
            $sName = Helper::translate('WRAPPING');
            $oItem = self::_createItem(
                $sName,
                $oWrappingCost->getBruttoPrice(),
                $sCurrency,
                1,
                $sName,
                $sName,
                $oWrappingCost->getVat(),
                $oWrappingCost->getVatValue()
            );

            $oWdBasket->add($oItem);
        }
    }

    /**
     * Adds all voucher discounts to the basket
     *
     * @param WdBasket $oWdBasket the paymentSDK basket item
     * @param Basket   $oBasket   the OXID basket item
     * @param string   $sCurrency
     *
     * @since 1.0.0
     */
    public static function addVoucherDiscountsToBasket(&$oWdBasket, $oBasket, $sCurrency)
    {
        /**
         * @var Voucher[] $aVouchers
         */
        $aVouchers = $oBasket->getVouchers();

        if (count($aVouchers) > 0) {
            foreach ($aVouchers as $oVoucher) {
                $sName = Helper::translate('COUPON');
                $oItem = self::_createItem(
                    $sName,
                    $oVoucher->dVoucherdiscount * -1,
                    $sCurrency,
                    1,
                    $sName,
                    $sName
                );

                $oWdBasket->add($oItem);
            }
        }
    }

    /**
     * Adds the shipping costs to the basket
     *
     * @param WdBasket $oWdBasket the paymentSDK basket item
     * @param Basket   $oBasket   the OXID basket item
     * @param string   $sCurrency
     *
     * @since 1.0.0
     */
    public static function addShippingCostsToBasket(&$oWdBasket, $oBasket, $sCurrency)
    {
        $oShippingCost = $oBasket->getDeliveryCost();

        $fPrice = 0;
        $fVat = 0;
        $fVatValue = 0;

        if ($oShippingCost && !empty($oShippingCost->getPrice())) {
            $fPrice = $oShippingCost->getBruttoPrice();
            $fVatValue = $oShippingCost->getVatValue();
            $fVat = $oShippingCost->getVat();
        }

        $sName = Helper::translate('wdpg_shipping_title');
        $oItem = self::_createItem(
            $sName,
            $fPrice,
            $sCurrency,
            1,
            $sName,
            $sName,
            $fVat,
            $fVatValue
        );

        $oWdBasket->add($oItem);
    }

    /**
     * @param WdBasket $oWdBasket
     * @param Basket   $oBasket
     * @param string   $sCurrencyName
     *
     * @since 1.0.0
     */
    public static function addDiscountsToBasket(&$oWdBasket, $oBasket, $sCurrencyName)
    {
        $aDiscounts = $oBasket->getDiscounts();

        if (count($aDiscounts) > 0) {
            foreach ($aDiscounts as $oDiscount) {
                $oItem = self::_createItem(
                    $oDiscount->sDiscount,
                    $oDiscount->dDiscount * -1,
                    $sCurrencyName,
                    1,
                    $oDiscount->sOXID,
                    $oDiscount->sDiscount
                );

                $oWdBasket->add($oItem);
            }
        }
    }

    /**
     * Adds an article to the basket
     *
     * @param WdBasket   $oBasket
     * @param BasketItem $oBasketItem
     * @param string     $sCurrency
     *
     * @throws ArticleInputException
     * @throws NoArticleException
     *
     * @since 1.0.0
     */
    public static function addArticleToBasket(&$oBasket, $oBasketItem, $sCurrency)
    {
        $oArticle = $oBasketItem->getArticle();
        $oItemPrice = $oBasketItem->getUnitPrice();
        $iQuantity = $oBasketItem->getAmount();
        $oItem = self::_createItem(
            $oArticle->oxarticles__oxtitle->value,
            $oItemPrice->getBruttoPrice(),
            $sCurrency,
            $iQuantity,
            $oArticle->oxarticles__oxartnum->value,
            $oArticle->oxarticles__oxshortdesc->value,
            $oItemPrice->getVat(),
            $oItemPrice->getVatValue()
        );
        $oBasket->add($oItem);
    }

    /**
     * @param string     $sName
     * @param float      $fPrice
     * @param string     $sCurrency
     * @param int        $iQuantity
     * @param string     $sArticleNumber
     * @param string     $sDescription
     * @param float|null $fTaxRate
     * @param float|null $fTaxValue
     *
     * @return Item
     *
     * @since 1.0.0
     */
    private static function _createItem(
        $sName,
        $fPrice,
        $sCurrency,
        $iQuantity,
        $sArticleNumber,
        $sDescription,
        $fTaxRate = null,
        $fTaxValue = null
    ) {
        $oItem = new Item(
            $sName,
            new Amount(Registry::getUtils()->fround($fPrice), $sCurrency),
            $iQuantity
        );

        $oItem->setArticleNumber($sArticleNumber);
        $oItem->setDescription($sDescription);

        if (!is_null($fTaxRate)) {
            $oItem->setTaxRate($fTaxRate);
        }

        if (!is_null($fTaxValue)) {
            $oItem->setTaxAmount(new Amount(Registry::getUtils()->fround($fTaxValue), $sCurrency));
        }

        return $oItem;
    }
}
