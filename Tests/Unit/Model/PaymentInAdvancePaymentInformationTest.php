<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\PaymentInAdvancePaymentInformation;

class PaymentInAdvancePaymentInformationTest extends OxidEsales\TestingLibrary\UnitTestCase
{

    protected function setUp()
    {
        parent::setUp();
    }

    public function testCorrectConstructor()
    {
        $oPiaPaymentInfo = new PaymentInAdvancePaymentInformation(
            "130",
            "DE82512308000005599148",
            "WIREDEMMXXX",
            "B0F6E7A153"
        );
        $this->assertNotEmpty($oPiaPaymentInfo->sAmount);
        $this->assertNotEmpty($oPiaPaymentInfo->sIban);
        $this->assertNotEmpty($oPiaPaymentInfo->sBic);
        $this->assertNotEmpty($oPiaPaymentInfo->sTransactionRefId);
    }

}
