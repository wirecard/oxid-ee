<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Field;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Extend\Model\Basket;
use Wirecard\Oxid\Extend\Model\Order;
use Wirecard\Oxid\Model\PaymentMethod\PayolutionBtwobPaymentMethod;

use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\PayolutionBtwobTransaction;

class PayolutionBtwobPaymentMethodTest extends \Wirecard\Test\WdUnitTestCase
{
    /**
     * @var PayolutionBtwobPaymentMethod
     */
    private $_oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->_oPaymentMethod = new PayolutionBtwobPaymentMethod();
    }

    public function testGetConfig()
    {
        $oConfig = $this->_oPaymentMethod->getConfig();
        $this->assertInstanceOf(PaymentMethodConfig::class, $oConfig->get('payolution-b2b'));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $this->assertInstanceOf(PayolutionBtwobTransaction::class, $oTransaction);
    }

    public function testAddMandatoryTransactionData()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $oTransaction->setOperation(Operation::RESERVE);
        $sCompanyName = 'My Awesome Company';
        $aDynvalues['wdCompanyName'] = $sCompanyName;
        $this->getSession()->setVariable('dynvalue', $aDynvalues);
        $oOrderStub = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $oOrderStub->method('__get')
            ->with('oxorder__oxpaymenttype')
            ->willReturn(new Field(PayolutionBtwobPaymentMethod::getName()));
        $oOrderStub->method('getAccountHolder')
            ->willReturn(\Wirecard\Oxid\Core\AccountHolderHelper::createAccountHolder(
                [
                    'firstName' => 'First Name',
                    'lastName' => 'Last Name',
                    'countryCode' => 'AT',
                    'street' => 'Street 1',
                    'city' => 'Graz',
                ]
            ));
        $userStub = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sUstId = 'my USt Id';
        $userStub->method('__get')
            ->with('oxuser__oxustid')
            ->willReturn(new Field($sUstId));
        $oOrderStub->method('getOrderUser')
            ->willReturn($userStub);
        $this->_oPaymentMethod->addMandatoryTransactionData($oTransaction, $oOrderStub);
        $this->assertArraySubset(
            [
                'account-holder' => [
                    'last-name' => 'Last Name',
                    'first-name' => 'First Name',
                    'address' => [
                        'street1' => 'Street 1',
                        'city' => 'Graz',
                        'country' => 'AT',
                    ],
                ],
                'custom-fields' => [
                    'custom-field' => [
                        [
                            'field-name' => 'company-name',
                            'field-value' => $sCompanyName,
                        ],
                        [
                            'field-name' => 'company-uid',
                            'field-value' => $sUstId,
                        ],
                    ],
                ],
            ],
            $oTransaction->mappedProperties()
        );
    }

    public function testGetHiddenAccountHolderFields()
    {
        $aResult = $this->_oPaymentMethod->getHiddenAccountHolderFields();
        $this->assertEquals(['dateOfBirth'], $aResult);
    }

    public function testGetCheckoutFields()
    {
        $aCheckoutFields = $this->_oPaymentMethod->getCheckoutFields();
        $this->assertEquals(
            [
                'wdCompanyName' => [
                    'type' => 'text',
                    'title' => Helper::translate('wd_company_name_input'),
                    'required' => true,
                ],
            ],
            $aCheckoutFields
        );
    }

    public function testIsPaymentPossible()
    {
        $oBasketStub = $this->getMockBuilder(Basket::class)
            ->disableOriginalConstructor()
            ->getMock();
        $oBasketStub->method('getBasketArticles')
            ->willReturn([]);
        $oBasketStub->method('getVouchers')
            ->willReturn([]);
        $oCurrency = new stdClass();
        $oCurrency->name = 'EUR';
        $oBasketStub->method('getBasketCurrency')
            ->willReturn($oCurrency);
        $oSession = $this->getSession();
        $oSession->setBasket($oBasketStub);
        $oUserStub = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['__get'])
            ->getMock();
        $oUserStub->method('__get')
            ->with('oxuser__oxcountryid')
            ->willReturn(new Field('a7c40f631fc920687.20179984'));
        $oSession->setUser($oUserStub);
        $bResult = $this->_oPaymentMethod->isPaymentPossible();
        $this->assertTrue($bResult);
    }

    public function testOnBeforeOrderCreation()
    {
        $aDynvalues = [
            'wdCompanyName' => "My Awesome Company Inc."
        ];
        $this->getSession()->setVariable('dynvalue', $aDynvalues);
        try {
            $this->_oPaymentMethod->onBeforeOrderCreation();
        } catch (\OxidEsales\Eshop\Core\Exception\InputException $exc) {
            $this->fail("Exception thrown: " . get_class($exc));
        }
    }
}
