<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Address;

/**
 * Helper class to handle account holders for transactions
 *
 * @since 1.0.0
 */
class AccountHolderHelper
{
    /**
     * Creates an AccountHolder object from an array of arguments.
     *
     * @param array $aArgs
     * @return AccountHolder
     *
     * @since 1.0.0
     */
    public static function createAccountHolder($aArgs)
    {
        $oAccountHolder = new AccountHolder();

        $oAccountHolder->setAddress(self::_createAddress($aArgs));
        $oAccountHolder->setFirstName($aArgs['firstName']);
        $oAccountHolder->setLastName($aArgs['lastName']);
        $oAccountHolder->setEmail($aArgs['email']);

        $aOptionals = [
            'crmId' => 'setCrmId',
            'phone' => 'setPhone',
            'gender' => 'setGender',
        ];

        foreach ($aOptionals as $sField => $sSeter) {
            if (Helper::isPresentProperty($aArgs, $sField)) {
                $oAccountHolder->$sSeter($aArgs[$sField]);
            }
        }

        if (isset($aArgs['dateOfBirth'])) {
            $oAccountHolder->setDateOfBirth($aArgs['dateOfBirth']);
        }

        return $oAccountHolder;
    }

    /**
     * Creates an Address object from an array of arguments.
     *
     * @param array $aArgs
     * @return Address
     *
     * @since 1.0.0
     */
    private static function _createAddress($aArgs)
    {
        $oAddress = new Address(
            $aArgs['countryCode'] ?? '',
            $aArgs['city'] ?? '',
            $aArgs['street'] ?? ''
        );

        if (Helper::isPresentProperty($aArgs, 'postalCode')) {
            $oAddress->setPostalCode($aArgs['postalCode']);
        }

        if (Helper::isPresentProperty($aArgs, 'state')) {
            $oAddress->setState($aArgs['state']);
        }

        return $oAddress;
    }
}
