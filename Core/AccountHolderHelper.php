<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use Wirecard\Oxid\Core\Helper;
use Wirecard\PaymentSdk\Entity\Address;
use Wirecard\PaymentSdk\Entity\AccountHolder;

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
    public function createAccountHolder(array $aArgs): AccountHolder
    {
        $oAccountHolder = new AccountHolder();

        $oAccountHolder->setAddress($this->_createAddress($aArgs));
        $oAccountHolder->setFirstName($aArgs['firstName']);
        $oAccountHolder->setLastName($aArgs['lastName']);
        $oAccountHolder->setEmail($aArgs['email']);

        if (Helper::isPresentProperty($aArgs, 'phone')) {
            $oAccountHolder->setPhone($aArgs['phone']);
        }

        if (Helper::isPresentProperty($aArgs, 'gender')) {
            $oAccountHolder->setGender($aArgs['gender']);
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
    private function _createAddress(array $aArgs): Address
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
