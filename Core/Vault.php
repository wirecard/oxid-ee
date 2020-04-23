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
     * @param Order $oOrder
     *
     * @return array of cards with keys 'OXID', 'USERID', 'ADDRESSID', 'TOKEN', 'MASKEDPAN', 'EXPIRATIONMONTH',
     *         'EXPIRATIONYEAR', 'CREATED', 'MODIFIED'
     *
     * @throws DatabaseConnectionException
     * @since 1.3.0
     */
    public static function getCards($oOrder)
    {
        $sUserId = $oOrder->getOrderUser()->oxuser__oxid->value;
        $aCards = self::_getCardsFromDb($sUserId, self::_getAddressId($oOrder));

        return array_filter($aCards, function ($aCard) {
            $oDateExpiration = new DateTime($aCard['EXPIRATIONYEAR'] . '-' . $aCard['EXPIRATIONMONTH'] . '-01');
            $oDateExpiration->add(new DateInterval('P1M'));
            $oDateToday = new DateTime();

            $bIsValid = $oDateToday < $oDateExpiration;
            if (!$bIsValid) {
                self::deleteCard($aCard['USERID'], $aCard['OXID']);
            }

            return $bIsValid;
        });
    }

    /**
     * Get card by token for a user
     *
     * @param string $sUserId
     * @param string $sTokenId
     *
     * @return array|null
     *
     * @throws DatabaseConnectionException
     * @since 1.3.0
     */
    public static function getCardByToken($sUserId, $sTokenId)
    {
        $aRow = null;
        try {
            $sQuery = "SELECT * from " . OxidEeEvents::VAULT_TABLE . " WHERE `USERID`=? AND `TOKEN`=?";
            $aRow = self::_getDb()->getRow($sQuery, [$sUserId, $sTokenId]);
            if (empty($aRow)) {
                $aRow = null;
            }
        } catch (DatabaseErrorException $oExc) {
            Registry::getLogger()->error("Error getting card by token", [$oExc]);
        }

        return $aRow;
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
                WHERE `USERID`=? AND `ADDRESSID`=? ORDER BY `OXID` DESC";

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
     * @param Order           $oOrder
     *
     * @return void
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     *
     * @since 1.3.0
     */
    public static function saveCard($oResponse, $aCard, $oOrder)
    {
        $aVaultCard = [
            'token' => $oResponse->getCardTokenId(),
            'maskedPan' => $oResponse->getMaskedAccountNumber(),
            'expirationMonth' => $aCard['expiration-month'],
            'expirationYear' => $aCard['expiration-year'],
            'userId' => $oOrder->getOrderUser()->oxuser__oxid->value,
            'addressId' => self::_getAddressId($oOrder),
        ];

        // needed for testing only
        $aVaultCard['created'] = isset($aCard['created']) ? $aCard['created'] : (new DateTime())->format('Y-m-d H:i:s');

        $aExistingCards = self::getCards($oOrder);
        foreach ($aExistingCards as $aCard) {
            if (self::_areCardsEqual($aCard, $aVaultCard)) {
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
            `EXPIRATIONYEAR`=?,
            `CREATED`=?";

        self::_getDb()->execute($sQuery, [
            $aCard['userId'],
            $aCard['addressId'],
            $aCard['token'],
            $aCard['maskedPan'],
            $aCard['expirationMonth'],
            $aCard['expirationYear'],
            $aCard['created'],
        ]);
    }

    /**
     * Delete the card from the Vault
     *
     * @param string $sUserId
     * @param string $sVaultId
     *
     * @return int
     *
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     *
     * @since 1.3.0
     */
    public static function deleteCard($sUserId, $sVaultId)
    {
        $sQuery = "DELETE FROM " . OxidEeEvents::VAULT_TABLE . " 
            WHERE `USERID`=? AND `OXID`=?";

        return self::_getDb()->execute($sQuery, [
            $sUserId,
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

    /**
     * @param array $aCard
     * @param array $aVaultCard
     *
     * @return bool
     *
     * @since 1.3.0
     */
    private static function _areCardsEqual($aCard, $aVaultCard)
    {
        return $aCard['TOKEN'] === $aVaultCard['token']
            && $aCard['ADDRESSID'] === $aVaultCard['addressId']
            && $aCard['USERID'] === $aVaultCard['userId'];
    }
}
