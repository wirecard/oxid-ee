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
    const AMOUNT = "130";
    const IBAN = "DE82512308000005599148";
    const BIC = "WIREDEMMXXX";
    const TRANSACTION_REF_ID = "B0F6E7A153";

    protected function setUp()
    {
        parent::setUp();
    }

    public function testCorrectConstructor()
    {
        $oPiaPaymentInfo = new PaymentInAdvancePaymentInformation(
            self::AMOUNT,
            self::IBAN,
            self::BIC,
            self::TRANSACTION_REF_ID
        );
        $this->assertTrue($oPiaPaymentInfo->sAmount === self::AMOUNT);
    }

}
