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
     *
     * @return AccountInfo
     *
     * @since 1.3.0
     */
    public static function create($bIsLoggedIn, $sChallengeIndicator)
    {
        $oAccountInfo = new AccountInfo();
        $sAuthMethod = $bIsLoggedIn ? AuthMethod::USER_CHECKOUT : AuthMethod::GUEST_CHECKOUT;
        $oAccountInfo->setAuthMethod($sAuthMethod);
        // ToDo: currently do not send challenge requested on first "tokenize credit card", due to a workflow problem
        $oAccountInfo->setChallengeInd($sChallengeIndicator);
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
}
