<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\InputException;
use OxidEsales\Eshop\Core\Field;
use Wirecard\Oxid\Extend\Model\Payment;
use Wirecard\Oxid\Model\PayolutionInvoicePaymentMethod;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\PayolutionInvoiceTransaction;

class PayolutionInvoicePaymentMethodTest extends \Wirecard\Test\WdUnitTestCase
{
    /**
     * @var PayolutionInvoicePaymentMethod
     */
    private $_oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();

        $this->_oPaymentMethod = new PayolutionInvoicePaymentMethod();
    }

    public function testGetConfig()
    {
        $oConfig = $this->_oPaymentMethod->getConfig();

        $this->assertInstanceOf(PaymentMethodConfig::class, $oConfig->get(PayolutionInvoicePaymentMethod::getName()));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $this->assertInstanceOf(PayolutionInvoiceTransaction::class, $oTransaction);
    }

    public function testGetConfigFields()
    {
        $aFields = $this->_oPaymentMethod->getConfigFields();

        $this->assertEquals([
            'descriptor',
            'additionalInfo',
            'deleteCanceledOrder',
            'deleteFailedOrder',
            'shippingCountries',
            'billingCountries',
            'billingShipping',
            'terms',
            'payolutionTermsUrl',
            'allowedCurrencies',
            'apiUrl',
            'groupSeparator_eur',
            'httpUser_eur',
            'httpPassword_eur',
            'maid_eur',
            'secret_eur',
            'testCredentials_eur',
        ], array_keys($aFields));
    }

    public function testGetPublicFieldNames()
    {
        $aFieldNames = $this->_oPaymentMethod->getPublicFieldNames();

        $this->assertEquals([
            'apiUrl',
            'maid',
            'descriptor',
            'additionalInfo',
            'deleteCanceledOrder',
            'deleteFailedOrder',
            'shippingCountries',
            'billingCountries',
            'billingShipping',
            'terms',
            'payolutionTermsUrl',
        ], $aFieldNames);
    }

    public function testGetMetaDataFieldNames()
    {
        $aMinimumExpectedKeys = [
            'shipping_countries',
            'billing_countries',
            'billing_shipping',
            'allowed_currencies',
            'httpuser_eur',
            'httppass_eur',
            'maid_eur',
            'secret_eur',
            'terms',
            'payolution_terms_url',
        ];

        foreach ($aMinimumExpectedKeys as $sKey) {
            $this->assertContains($sKey, $this->_oPaymentMethod->getMetaDataFieldNames());
        }
    }

    public function testOnBeforeTransactionCreationWithRequestParameter()
    {
        $this->setRequestParameter('terms_checkbox', true);

        $this->assertNull($this->_oPaymentMethod->onBeforeTransactionCreation());
    }

    /**
     * @expectedException OxidEsales\Eshop\Core\Exception\InputException
     */
    public function testOnBeforeTransactionCreationWithoutRequestParameter()
    {
        $this->_oPaymentMethod->onBeforeTransactionCreation();
    }

    public function testAddMandatoryTransactionData()
    {
        $oUser = oxNew(User::class);
        $oUser->load('testuser');
        $oUser->oxuser__oxcountryid = new Field('a7c40f632cdd63c52.64272623');
        $oUser->save();
        $this->getSession()->setUser($oUser);

        $aDynvalues['phonepayolution-inv'] = '4512543425';
        $this->getSession()->setVariable('dynvalue', $aDynvalues);

        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $oOrder = oxNew(Order::class);
        $this->_oPaymentMethod->addMandatoryTransactionData($oTransaction, $oOrder);

        $this->assertObjectHasAttribute('shipping', $oTransaction);
    }
    /**
     * @dataProvider onBeforeOrderCreationProvider
     */
    public function testOnBeforeOrderCreation($sCountryId, $sPhone)
    {
        $oUser = oxNew(User::class);
        $oUser->load('testuser');
        $oUser->oxuser__oxcountryid = new Field($sCountryId);
        $oUser->save();
        $this->getSession()->setUser($oUser);

        $aDynvalues['dateOfBirthpayolution-inv'] = '12.12.1985';
        $aDynvalues['phonepayolution-inv'] = $sPhone;
        $aDynvalues['saveCheckoutFieldspayolution-inv'] = '1';
        $this->getSession()->setVariable('dynvalue', $aDynvalues);

        $this->_oPaymentMethod->onBeforeOrderCreation();
    }

    public function onBeforeOrderCreationProvider()
    {
        return [
            'user from netherlands with phone set' => [
                'countryId' => 'a7c40f632cdd63c52.64272623',
                'phone' => '65161651',
            ],
            'user not from austria no phone set' => [
                'countryId' => 'a7c40f6320aeb2ec2.72885259',
                'phone' => '',
            ],
        ];
    }
}
