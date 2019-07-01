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
     * @return array of cards with keys 'OXID', 'USERID', 'ADDRESSID', 'TOKEN', 'MASKEDPAN', 'EXPIRATIONMONTH',
     *         'EXPIRATIONYEAR'
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
        $aCards = self::_getCardsFromDb($sUserId, $sAddressId);

        return array_filter($aCards, function ($aCard) {
            $oDateExpiration = new DateTime($aCard['EXPIRATIONYEAR'] . '-' . $aCard['EXPIRATIONMONTH'] . '-01');
            $oDateExpiration->add(new DateInterval('P6M'));

            $oDateToday = new DateTime();

            return $oDateToday < $oDateExpiration;
        });
    }

    /**
     * @param string $sUserId
     * @param string $sAddressId
     *
     * @return array
     *
     * @throws DatabaseConnectionException
     *
     * @since 1.3.0
     */
    private static function _getCardsFromDb($sUserId, $sAddressId)
    {
        try {
            $sQuery = "SELECT * from " . OxidEeEvents::VAULT_TABLE . " 
                WHERE `USERID`=? AND `ADDRESSID`=?";
            return self::_getDb()->getAll($sQuery, [$sUserId, $sAddressId]);
        } catch (DatabaseErrorException $oExc) {
            Registry::getLogger()->error("Error getting cards", [$oExc]);
        }

        return [];
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
     *
     * @since 1.3.0
     */
    public static function saveCard($oResponse, $aCard, $oOrder)
    {
        $aVaultCard = [
            'token' => $oResponse->getCardTokenId(),
            'maskedPan' => $oResponse->getMaskedAccountNumber(),
            'exporationMonth' => $aCard['expiration-month'],
            'expirationYear' => $aCard['expiration-year'],
            'userId' => Registry::getSession()->getUser()->getId(),
            'addressId' => self::_getAddressId($oOrder),
        ];

        $aExistingCards = self::getCards($oOrder);
        foreach ($aExistingCards as $aCard) {
            if ($aCard['TOKEN'] === $aVaultCard['token'] && $aCard['ADDRESSID'] === $aVaultCard['addressId']) {
                return;
            }
        }
        self::_insertCard($aVaultCard);
    }

    /**
     * @param array $aCard
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     *
     * @since 1.3.0
     */
    private static function _insertCard($aCard)
    {
        $sQuery = "INSERT INTO " . OxidEeEvents::VAULT_TABLE . " SET
            `USERID`=?,
            `ADDRESSID`=?,
            `TOKEN`=?,
            `MASKEDPAN`=?,
            `EXPIRATIONMONTH`=?,
            `EXPIRATIONYEAR`=?";

        self::_getDb()->execute($sQuery, [
            $aCard['userId'],
            $aCard['addressId'],
            $aCard['token'],
            $aCard['maskedPan'],
            $aCard['exporationMonth'],
            $aCard['expirationYear'],
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

    /**
     * @param Order $oOrder
     *
     * @return string
     *
     * @since 1.3.0
     */
    private static function _getAddressId($oOrder)
    {
        return sha1(implode($oOrder->getShippingAccountHolder()->mappedProperties()));
    }
}
