<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use OxidEsales\Eshop\Core\Exception\InputException;

use Wirecard\Oxid\Core\BasketHelper;
use Wirecard\Oxid\Extend\Model\Order;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\PayolutionBtwobTransaction;

/**
 * Payment method implementation for Payolution B2B
 *
 * @since 1.3.0
 */
class PayolutionBtwobPaymentMethod extends PayolutionBasePaymentMethod
{
    /**
     * @inheritdoc
     *
     * @since 1.3.0
     */
    protected static $_sName = "payolution-b2b";

    /**
     * @inheritdoc
     *
     * @return Config
     *
     * @since 1.3.0
     */
    public function getConfig()
    {
        $oConfig = parent::getConfig();

        // get the currency-specific config values
        $sCurrency = BasketHelper::getCurrencyFromBasket();

        $sMaidField = 'oxpayments__maid_' . $sCurrency;
        $sSecretField = 'oxpayments__secret_' . $sCurrency;

        $oPaymentMethodConfig = new PaymentMethodConfig(
            PayolutionBtwobTransaction::NAME,
            $this->_oPayment->$sMaidField->value,
            $this->_oPayment->$sSecretField->value
        );

        $oConfig->add($oPaymentMethodConfig);

        return $oConfig;
    }

    /**
     * Get the payments method transaction configuration
     *
     * @return \Wirecard\PaymentSdk\Transaction\Transaction
     *
     * @since 1.3.0
     */
    public function getTransaction()
    {
        return new PayolutionBtwobTransaction();
    }

    /**
     * @inheritdoc
     *
     * @param PayolutionBtwobTransaction $oTransaction
     * @param Order                      $oOrder
     *
     * @throws \Exception
     *
     * @since 1.3.0
     */
    public function addMandatoryTransactionData(&$oTransaction, $oOrder)
    {
        $aAccountHolder = $oOrder->getAccountHolder();

        $oTransaction->setAccountHolder($aAccountHolder);
        $oCompanyInfo = new CompanyInfo(SessionHelper::getCompanyName());

        $sUid = $oOrder->getOrderUser()->oxuser__oxustid->value;
        if ($sUid) {
            $oCompanyInfo->setCompanyUid($sUid);
        }

        $oTransaction->setCompanyInfo($oCompanyInfo);
    }

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.3.0
     */
    public function getCheckoutFields()
    {
        return [
            'wdCompanyName' => [
                'type' => $this->_getCheckoutFieldType(SessionHelper::isCompanyNameSet()),
                'title' => Helper::translate('wd_company_name_input'),
                'required' => true,
            ],
        ];
    }

    /**
     * Checks if the user is older than 18 or the date of birth needs to be entered
     *
     * @return bool
     *
     * @throws \Exception
     *
     * @since 1.2.0
     */
    protected function _checkDateOfBirth()
    {
        if (!SessionHelper::isDateOfBirthSet(self::getName())) {
            //only check birthdate if set
            return true;
        }

        return SessionHelper::isUserOlderThan(18, self::getName());
    }

    /**
     * Validates the user input and throws a specific error if an input is wrong
     *
     * @throws /Exception
     *
     * @since  1.2.0
     */
    protected function _validateUserInput()
    {
        if (!SessionHelper::isCompanyNameSet()) {
            throw new InputException(Helper::translate('wd_text_generic_error'));
        }
    }

    /**
     * Get the keys that should not be included for this payment method
     *
     * @return array
     *
     * @since 1.3.0
     */
    public function hiddenAccountHolderFields()
    {
        return [
            'dateOfBirth',
        ];
    }
}
