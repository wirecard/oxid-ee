<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend\Model;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Model\Paypal_Payment_Method;

use Wirecard\PaymentSdk\Entity\Address;
use Wirecard\PaymentSdk\Entity\AccountHolder;

use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Payment;

/**
 * Class Order
 *
 * @package Wirecard\Extend
 *
 * @mixin \OxidEsales\Eshop\Application\Model\Order
 */
class Order extends Order_parent
{
    const STATE_PENDING = 'pending';
    const STATE_AUTHORIZED = 'authorized';
    const STATE_PROCESSING = 'processing';
    const STATE_CANCELED = 'canceled';
    const STATE_REFUNDED = 'refunded';

    /**
     * Returns the country associated with the order billing address.
     *
     * @return Country
     */
    public function getOrderBillingCountry(): Country
    {
        $oCountry = oxNew(Country::class);
        $oCountry->load($this->oxorder__oxbillcountryid->value);

        return $oCountry;
    }

    /**
     * Returns the country associated with the order shipping address.
     *
     * @return Country
     */
    public function getOrderShippingCountry(): Country
    {
        $oCountry = oxNew(Country::class);
        $oCountry->load($this->oxorder__oxdelcountryid->value);

        return $oCountry;
    }

    /**
     * Returns the payment associated with the order.
     *
     * @return Payment
     */
    public function getOrderPayment(): Payment
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->load($this->oxorder__oxpaymenttype->value);

        return $oPayment;
    }

    /**
     * Returns an associative array of available states and their translation.
     *
     * @return array
     */
    public static function getTranslatedStates(): array
    {
        return [
            self::STATE_PENDING => Helper::translate('order_status_pending'),
            self::STATE_AUTHORIZED => Helper::translate('order_status_authorized'),
            self::STATE_PROCESSING => Helper::translate('order_status_purchased'),
            self::STATE_CANCELED => Helper::translate('order_status_cancelled'),
            self::STATE_REFUNDED => Helper::translate('order_status_refunded'),
        ];
    }

    /**
     * Creates an AccountHolder object from an array of arguments.
     *
     * @return AccountHolder
     */
    protected function _createAccountHolder(array $aArgs): AccountHolder
    {
        $oAccountHolder = new AccountHolder();

        foreach ([
            'firstName'      => 'setFirstName',
            'lastName'       => 'setLastName',
            'email'          => 'setEmail',
            'phone'          => 'setPhone',
            'gender'         => 'setGender',
            'dateOfBirth'    => 'setDateOfBirth',
        ] as $sKey => $sMethodName) {
            if (!empty($aArgs[$sKey])) {
                $oAccountHolder->$sMethodName($aArgs[$sKey]);
            }
        }

        $oAccountHolder->setAddress($this->_createAddress($aArgs));

        return $oAccountHolder;
    }

    /**
     * Creates an Address object from an array of arguments.
     *
     * @param array $aArgs
     * @return Address
     */
    protected function _createAddress(array $aArgs): Address
    {
        $oAddress = new Address(
            $aArgs['countryCode'] ?? '',
            $aArgs['city'] ?? '',
            $aArgs['street'] ?? ''
        );

        foreach ([
            'state'          => 'setState',
            'postalCode'     => 'setPostalCode',
        ] as $sKey => $sMethodName) {
            if (!empty($aArgs[$sKey])) {
                $oAddress->$sMethodName($aArgs[$sKey]);
            }
        }

        return $oAddress;
    }

    /**
     * Creates an AccountHolder object for the order.
     *
     * @return AccountHolder
     */
    public function getAccountHolder(): AccountHolder
    {
        $oCountry = $this->getOrderBillingCountry();
        $oUser = $this->getOrderUser();

        return $this->_createAccountHolder([
            'countryCode' => $oCountry->oxcountry__oxisoalpha2->value,
            'city' => $this->oxorder__oxbillcity->value,
            'street' => $this->oxorder__oxbillstreet->value . ' ' . $this->oxorder__oxbillstreetnr->value,
            'state' => $this->oxorder__oxbillstateid->value,
            'postalCode' => $this->oxorder__oxbillzip->value,
            'firstName' => $this->oxorder__oxbillfname->value,
            'lastName' => $this->oxorder__oxbilllname->value,
            'email' => $this->oxorder__oxbillemail->value,
            'phone' => $this->oxorder__oxbillfon->value,
            'gender' => Helper::getGenderCodeForSalutation($this->oxorder__oxbillsal->value),
            'dateOfBirth' => Helper::getDateTimeFromString($oUser->oxuser__oxbirthdate->value),
        ]);
    }

    /**
     * Creates a shipping AccountHolder object for the order.
     *
     * @return AccountHolder
     */
    public function getShippingAccountHolder(): AccountHolder
    {
        // use shipping info if available
        $oCountry = $this->getOrderShippingCountry();
        if (!empty($oCountry->oxcountry__oxisoalpha2->value)) {
            return $this->_createAccountHolder([
                'countryCode' => $oCountry->oxcountry__oxisoalpha2->value,
                'city' => $this->oxorder__oxdelcity->value,
                'street' => $this->oxorder__oxdelstreet->value . ' ' . $this->oxorder__oxdelstreetnr->value,
                'state' => $this->oxorder__oxdelstateid->value,
                'postalCode' => $this->oxorder__oxdelzip->value,
                'firstName' => $this->oxorder__oxdelfname->value,
                'lastName' => $this->oxorder__oxdellname->value,
                'phone' => $this->oxorder__oxdelfon->value,
            ]);
        }

        // fallback to billing info
        $oCountry = $this->getOrderBillingCountry();
        return $this->_createAccountHolder([
            'countryCode' => $oCountry->oxcountry__oxisoalpha2->value,
            'city' => $this->oxorder__oxbillcity->value,
            'street' => $this->oxorder__oxbillstreet->value . ' ' . $this->oxorder__oxbillstreetnr->value,
            'state' => $this->oxorder__oxbillstateid->value,
            'postalCode' => $this->oxorder__oxbillzip->value,
            'firstName' => $this->oxorder__oxbillfname->value,
            'lastName' => $this->oxorder__oxbilllname->value,
            'phone' => $this->oxorder__oxbillfon->value,
        ]);
    }

    /**
     * Returns an array of available states.
     *
     * @return array
     */
    public static function getStates(): array
    {
        return array_keys(self::getTranslatedStates());
    }

    /**
     * Returns the translation for the order's state.
     *
     * @return string
     */
    public function getTranslatedState(): string
    {
        return self::getTranslatedStates()[$this->oxorder__wdoxidee_orderstate->value] ?? '';
    }
}
