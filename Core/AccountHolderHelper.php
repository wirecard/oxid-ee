<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use Wirecard\PaymentSdk\Entity\Address;
use Wirecard\PaymentSdk\Entity\AccountHolder;

/**
 * Helper class to handle account holders for transactions
 */
class AccountHolderHelper
{
    /**
     * Creates an AccountHolder object from an array of arguments.
     *
     * @param array $aArgs
     * @return AccountHolder
     */
    public function createAccountHolder(array $aArgs): AccountHolder
    {
        $oAccountHolder = new AccountHolder();

        $oAccountHolder->setAddress($this->_createAddress($aArgs));
        $oAccountHolder->setFirstName($aArgs['firstName']);
        $oAccountHolder->setLastName($aArgs['lastName']);
        $oAccountHolder->setEmail($aArgs['email']);

        if (isset($aArgs['phone']) && !empty($aArgs['phone'])) {
            $oAccountHolder->setPhone($aArgs['phone']);
        }

        if (isset($aArgs['gender']) && !empty($aArgs['gender'])) {
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
     */
    private function _createAddress(array $aArgs): Address
    {
        $oAddress = new Address(
            $aArgs['countryCode'] ?? '',
            $aArgs['city'] ?? '',
            $aArgs['street'] ?? ''
        );

        if (isset($aArgs['postalCode']) && !empty($aArgs['postalCode'])) {
            $oAddress->setPostalCode($aArgs['postalCode']);
        }

        if (isset($aArgs['state']) && !empty($aArgs['state'])) {
            $oAddress->setState($aArgs['state']);
        }

        return $oAddress;
    }
}
