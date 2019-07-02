<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use DateInterval;
use DateTime;

use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;

use Wirecard\PaymentSdk\Response\SuccessResponse;

/**
 * Vault for saving credit card information
 *
 * @since 1.3.0
 */
class Vault
{
    /**
     * Get all cards associated with the user and address
     *
     * @param string $sUserId
     * @param string $sAddressId
     *
     * @return array of cards with keys
     * 'OXID'
     * 'USERID'
     * 'ADDRESSID'
     * 'TOKEN'
     * 'MASKEDPAN'
     * 'EXPIRATIONMONTH'
     * 'EXPIRATIONYEAR'
     *
     * @throws DatabaseConnectionException
     *
     * @since 1.3.0
     */
    public static function getCards()
    {
        $oUser = Registry::getSession()->getUser();
        $sUserId = $oUser->getId();
        $sAddressId = $oUser->getSelectedAddressId();
        $aCards = [];
        try {
            $sQuery = "SELECT * from " . OxidEeEvents::VAULT_TABLE . " " .
                "WHERE `USERID`=? AND `ADDRESSID`=?";
            $aCards = self::_getDb()->getAll($sQuery, [$sUserId, $sAddressId]);
        } catch (DatabaseErrorException $oExc) {
            Registry::getLogger()->error("Error getting cards", [$oExc]);
        }

        return array_filter($aCards, function ($aCard) {
            $oDateExpiration = new DateTime($aCard['EXPIRATIONYEAR'] . '-' . $aCard['EXPIRATIONMONTH'] . '-01');
            $oDateExpiration->add(new DateInterval('P6M'));

            $oDateToday = new DateTime();

            return $oDateToday < $oDateExpiration;
        });
    }

    /**
     * Save a card to the Vault
     *
     * @param SuccessResponse $oResponse
     * @param array           $aCard
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     *
     * @return void
     * @since 1.3.0
     */
    public static function saveCard($oResponse, $aCard)
    {
        $sToken = $oResponse->getCardTokenId();
        $sMaskedPan = $oResponse->getMaskedAccountNumber();
        $iExporationMonth = $aCard['expiration-month'];
        $iExpirationYear = $aCard['expiration-year'];
        $oUser = Registry::getSession()->getUser();
        $sUserId = $oUser->getId();
        $sAddressId = $oUser->getSelectedAddressId();

        $aExistingCards = self::getCards($sUserId, $sAddressId);
        foreach ($aExistingCards as $aCard) {
            if ($aCard['TOKEN'] == $sToken && $aCard['ADDRESSID'] == $sAddressId) {
                return;
            }
        }

        $sQuery = "INSERT INTO " . OxidEeEvents::VAULT_TABLE . " SET
            `USERID`=?,
            `ADDRESSID`=?,
            `TOKEN`=?,
            `MASKEDPAN`=?,
            `EXPIRATIONMONTH`=?,
            `EXPIRATIONYEAR`=?";

        self::_getDb()->execute($sQuery, [
            $sUserId,
            $sAddressId,
            $sToken,
            $sMaskedPan,
            $iExporationMonth,
            $iExpirationYear,
        ]);
    }

    /**
     * Delete the card from the Vault
     *
     * @param string $sVaultId
     *
     * @return int
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     *
     * @since 1.3.0
     */
    public static function deleteCard($sVaultId)
    {
        $sQuery = "DELETE FROM " . OxidEeEvents::VAULT_TABLE . " 
            WHERE `USERID`=? AND `OXID`=?";

        return self::_getDb()->execute($sQuery, [
            Registry::getSession()->getUser()->getId(),
            $sVaultId,
        ]);
    }

    /**
     * Get the database object
     *
     * @return DatabaseInterface
     *
     * @throws DatabaseConnectionException
     *
     * @since 1.3.0
     */
    private static function _getDb()
    {
        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
    }
}
