<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use Wirecard\Oxid\Core\BasketHelper;
use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\SessionHelper;
use Wirecard\Oxid\Extend\Model\Order;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Transaction\PayolutionBtwobTransaction;
use Wirecard\PaymentSdk\Transaction\PayolutionInvoiceTransaction;

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
     * @param PayolutionInvoiceTransaction $oTransaction
     * @param Order                        $oOrder
     *
     * @throws \Exception
     *
     * @since 1.3.0
     */
    public function addMandatoryTransactionData(&$oTransaction, $oOrder)
    {
        $oTransaction->setCustomFields($this->_getCustomFields($oOrder));
    }

    /**
     * Generate the custom fields collection with the company info
     *
     * @param Order $oOrder
     *
     * @return CustomFieldCollection
     *
     * @since 1.3.0
     */
    private function _getCustomFields($oOrder)
    {
        $oCustomFields = new CustomFieldCollection();
        $oCustomFields->add(new CustomField('company-name', SessionHelper::getCompanyName()));

        $sUid = $oOrder->getOrderUser()->oxuser__oxustid;

        if ($sUid) {
            $oCustomFields->add(new CustomField('company-uid', $sUid));
        }
        return $oCustomFields;
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
}
