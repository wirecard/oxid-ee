<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Core\PaymentMethodHelper;
use Wirecard\Oxid\Extend\Model\Basket;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\User;

class PaymentMethodHelperTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var PaymentMethodHelper
     */
    private $_oPaymentMethodHelper;

    protected function setUp()
    {
        parent::setUp();
        $this->_oPaymentMethodHelper = new PaymentMethodHelper();
    }

    public function testGetSepaMandateHtml()
    {
        $oBasketStub = $this->getMockBuilder(Basket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oUserStub = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aDynArray['iban'] = 'DE42512308000000060004';
        Registry::getSession()->setVariable('dynvalue', $aDynArray);

        $sSepaMandate = $this->_oPaymentMethodHelper->getSepaMandateHtml($oBasketStub, $oUserStub);
        $this->assertContains('DE42512308000000060004', $sSepaMandate);
    }

}
