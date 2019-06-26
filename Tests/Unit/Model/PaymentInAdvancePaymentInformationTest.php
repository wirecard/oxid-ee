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
    private $_sAmount = "130";
    private $_sIban = "DE82512308000005599148";
    private $_sBic = "WIREDEMMXXX";
    private $_sTransactionRefId = "B0F6E7A153";

    protected function setUp()
    {
        parent::setUp();
    }

    public function testCorrectConstructor()
    {
        $oPiaPaymentInfo = new PaymentInAdvancePaymentInformation(
            $this->_sAmount,
            $this->_sIban,
            $this->_sBic,
            $this->_sTransactionRefId
        );
        $this->assertNotEmpty($oPiaPaymentInfo->sAmount);
        $this->assertNotEmpty($oPiaPaymentInfo->sIban);
        $this->assertNotEmpty($oPiaPaymentInfo->sBic);
        $this->assertNotEmpty($oPiaPaymentInfo->sTransactionRefId);
    }

}
