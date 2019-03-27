<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend;

use OxidEsales\Eshop\Application\Model\BasketItem;
use OxidEsales\EshopCommunity\Core\Registry;
use Wirecard\Oxid\Core\BasketHelper;
use Wirecard\PaymentSdk\Entity\Basket as TransactionBasket;

/**
 * Basket extension
 *
 * @mixin \OxidEsales\Eshop\Application\Model\Basket
 */
class Basket extends Basket_parent
{
    /**
     *
     * Creates a Basket to use in the Transaction
     *
     * @return TransactionBasket
     */
    public function createTransactionBasket()
    {
        /**
         * @var BasketItem[] $aBasketItems
         */
        $aBasketItems = $this->getContents();

        $oTransactionBasket = new TransactionBasket();

        $sCurrencyName = $this->getBasketCurrency()->name;

        if (count($aBasketItems) > 0) {
            foreach ($aBasketItems as $oBasketItem) {
                BasketHelper::addArticleToBasket($oTransactionBasket, $oBasketItem, $sCurrencyName);
            }
        }

        BasketHelper::addShippingCostsToBasket($oTransactionBasket, $this, $sCurrencyName);
        BasketHelper::addDiscountsToBasket($oTransactionBasket, $this, $sCurrencyName);
        BasketHelper::addVoucherDiscountsToBasket($oTransactionBasket, $this, $sCurrencyName);
        BasketHelper::addWrappingCostsToBasket($oTransactionBasket, $this, $sCurrencyName);
        BasketHelper::addGiftCardCostsToBasket($oTransactionBasket, $this, $sCurrencyName);
        BasketHelper::addPaymentCostsToBasket($oTransactionBasket, $this, $sCurrencyName);
        return $oTransactionBasket;
    }
}
