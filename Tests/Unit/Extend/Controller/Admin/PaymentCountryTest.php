<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Extend\Controller\Admin\PaymentCountry;

class PaymentCountryTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var PaymentCountry
     */
    private $oPaymentCountry;

    protected function setUp()
    {
        parent::setUp();

        $this->oPaymentCountry = oxNew(PaymentCountry::class);
    }

    /**
     * @dataProvider renderProvider
     */
    public function testRender($sPaymentId, $bAllowCountryAssignment)
    {
        $_POST['oxid'] = $sPaymentId;

        $this->oPaymentCountry->render();

        $aViewData = $this->oPaymentCountry->getViewData();

        if ($bAllowCountryAssignment) {
            $this->assertArrayNotHasKey('readonly', $aViewData);
        } else {
            $this->assertArrayHasKey('readonly', $aViewData);
        }
    }

    public function renderProvider()
    {
        return [
            'allow country assignment' => [
                'oxidpayadvance',
                true,
            ],
            'disallow country assignment' => [
                'wdratepay-invoice',
                false,
            ],
        ];
    }
}
