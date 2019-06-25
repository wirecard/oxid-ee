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
        $oTransaction->setAccountHolder($oOrder->getAccountHolder());
        $oCompanyInfo = new CompanyInfo(SessionHelper::getCompanyName());

        $sUid = $oOrder->getOrderUser()->oxuser__oxustid;
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
}
