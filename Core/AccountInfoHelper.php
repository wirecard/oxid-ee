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
use Wirecard\PaymentSdk\Constant\AuthMethod;
use Wirecard\PaymentSdk\Constant\ChallengeInd;
use Wirecard\PaymentSdk\Entity\AccountInfo;

/**
 * Helper class to handle account holders for transactions
 *
 * @since 1.3.0
 */
class AccountInfoHelper
{

    /**
     * Creates an AccountHolder object from an array of arguments.
     *
     * @param bool   $bIsLoggedIn
     * @param string $sChallengeIndicator
     * @param bool   $bIsNewToken
     *
     * @return AccountInfo
     *
     * @since 1.3.0
     */
    public static function create($bIsLoggedIn, $sChallengeIndicator, $bIsNewToken)
    {
        $oAccountInfo = new AccountInfo();
        $sAuthMethod = $bIsLoggedIn ? AuthMethod::USER_CHECKOUT : AuthMethod::GUEST_CHECKOUT;
        $oAccountInfo->setAuthMethod($sAuthMethod);
        // ToDo: currently do not send challenge requested on first "tokenize credit card", due to a workflow problem
        $sChallengeIndicator = self::_getChallengeIndicator($sChallengeIndicator, $bIsNewToken);
        if ($sChallengeIndicator !== ChallengeInd::CHALLENGE_MANDATE) {
            $oAccountInfo->setChallengeInd($sChallengeIndicator);
        }

        return $oAccountInfo;
    }

    /**
     * @param AccountInfo   $oAccountInfo
     * @param bool          $bIsLoggedIn
     * @param string        $sCreatedAt
     * @param DateTime|null $oShippingFirstUsed
     * @param DateTime      $oCardCreationDate
     *
     * @return void
     * @throws \Exception
     * @since 1.3.0
     */
    public static function addAuthenticatedUserData(
        AccountInfo &$oAccountInfo,
        $bIsLoggedIn,
        $sCreatedAt,
        $oShippingFirstUsed,
        $oCardCreationDate
    ) {
        if (!$bIsLoggedIn) {
            return;
        }

        if (strlen($sCreatedAt)) {
            $oAccountInfo->setCreationDate(new DateTime($sCreatedAt));
        }

        if (!is_null($oShippingFirstUsed)) {
            $oAccountInfo->setShippingAddressFirstUse($oShippingFirstUsed);
        }

        $oAccountInfo->setCardCreationDate($oCardCreationDate);
    }

    /**
     * Get challenge indicator depending on existing token
     * - return config setting: for non one-click-checkout, guest checkout, existing token
     * - return 04/CHALLENGE_MANDATE: for new one-click-checkout token
     *
     * @param string $sChallengeIndicator
     * @param bool   $bIsNewToken
     *
     * @return string
     * @since 1.3.0
     */
    protected static function _getChallengeIndicator($sChallengeIndicator, $bIsNewToken)
    {
        // token id is null, for a new card token too, so check first for a new token
        // for non one-click-checkout $bIsNewToken is always false
        if ($bIsNewToken) {
            return ChallengeInd::CHALLENGE_MANDATE;
        }

        // existing token, guest checkout, non one-click checkout
        return $sChallengeIndicator;
    }
}
