<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use DateTime;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use Wirecard\Oxid\Extend\Model\Basket;
use Wirecard\Oxid\Extend\Model\Order;
use Wirecard\Oxid\Model\PaymentMethod\CreditCardPaymentMethod;
use Wirecard\PaymentSdk\Constant\ChallengeInd;

/**
 * Helper class to handle account holders for transactions
 *
 * @since 1.3.0
 */
class ThreedsHelper
{

    /**
     * @param Order $oOrder
     *
     * @return DateTime|null
     * @throws DatabaseConnectionException
     * @since 1.3.0
     */
    public static function getShippingAddressFirstUsed($oOrder)
    {
        $sDelCountryId = $oOrder->getFieldData('oxdelcountryid');

        $sWhich = strlen($sDelCountryId) ? 'oxdel' : 'oxbill';

        $aCompareFields = [
            'countryid',
            'city',
            'street',
            'streetnr',
            'stateid',
            'zip',
            'fname',
            'lname',
            'fon',
        ];

        $oDb = DatabaseProvider::getDb();

        $sTable = $oOrder->getViewName();

        $sQ = "select min(oxorderdate) from {$sTable} where %s";

        $sWhere = sprintf('oxuserid = %s', $oDb->quote($oOrder->getUser()->getId()));
        foreach ($aCompareFields as $sField) {
            $sFieldName = sprintf('%s%s', $sWhich, $sField);
            $sWhere .= sprintf(' AND %s = %s', $sFieldName, $oDb->quote($oOrder->getFieldData($sFieldName)));
        }

        $sQ = sprintf($sQ, $sWhere);

        $sFirstUsed = $oDb->getOne($sQ);

        if (!strlen($sFirstUsed)) {
            return null;
        }

        return new DateTime($sFirstUsed);
    }

    /**
     * creation date of used card token
     *
     * @param string $sUserId
     * @param string $sTokenId
     *
     * @return DateTime
     * @throws DatabaseConnectionException
     * @since 1.3.0
     */
    public static function getCardCreationDate($sUserId, $sTokenId)
    {
        if (is_null($sTokenId)) {
            return new DateTime();
        }

        $aCard = Vault::getCardByToken($sUserId, $sTokenId);

        if (is_null($aCard)) {
            return new DateTime();
        }

        return new DateTime($aCard['CREATED']);
    }

    /**
     * check if order has at least one reordered item
     *
     * @param Order  $oOrder
     * @param Basket $oBasket
     *
     * @return bool
     * @throws DatabaseConnectionException
     * @since 1.3.0
     */
    public static function hasReorderedItems($oOrder, $oBasket)
    {
        $oDb = DatabaseProvider::getDb();

        $aArticledIs = array_values(array_map(
            function ($oArticle) use ($oDb) {
                /** @var Article $oArticle */
                return $oArticle->getId();
            },
            $oBasket->getBasketArticles()
        ));

        $sTable = $oOrder->getViewName();

        $sQ = <<<SQL
            select count(*) from {$sTable} o
            join oxorderarticles oa on oa.oxorderid = o.oxid
            where o.oxuserid = ?
            and oa.oxartid IN (?)
SQL;

        return (int) $oDb->getOne($sQ, [$oOrder->getUser()->getId(), implode(', ', $aArticledIs)]);
    }

    /**
     * @param Basket $oBasket
     *
     * @return bool
     * @since 1.3.0
     */
    public static function hasDownloadableItems($oBasket)
    {
        foreach ($oBasket->getBasketArticles() as $oArticle) {
            /** @var Article $oArticle */
            if ($oArticle->isDownloadable()) {
                return true;
            }
        }

        return false;
    }

    /**
     * check whether a card token is a new one
     *
     * @param array $aDynValue
     * @param bool  $bSaveCredentials
     *
     * @return bool
     * @since 1.3.0
     */
    public static function isNewCardToken($aDynValue, $bSaveCredentials)
    {
        if ($bSaveCredentials) {
            return true;
        }

        if (isset($aDynValue[CreditCardPaymentMethod::CARD_TOKEN_FIELD]) &&
            $aDynValue[CreditCardPaymentMethod::CARD_TOKEN_FIELD] === CreditCardPaymentMethod::NEW_CARD_TOKEN) {
            return true;
        }

        return false;
    }

    /**
     * Returns an associative array of selectable actions and their translation.
     *
     * @return array
     * @since 1.3.0
     */
    public static function getTranslatedChallengeIndicators()
    {
        return [
            ChallengeInd::NO_PREFERENCE    => Helper::translate('wd_config_challenge_no_preference'),
            ChallengeInd::NO_CHALLENGE     => Helper::translate('wd_config_challenge_no_challenge'),
            ChallengeInd::CHALLENGE_THREED => Helper::translate('wd_config_challenge_challenge_threed'),
        ];
    }
}
