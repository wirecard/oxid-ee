<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;

class PaymentControllerTest extends \Wirecard\Test\WdUnitTestCase
{

    protected function setUp()
    {
        $this->addTableForCleanup('oxorder');
        parent::setUp();
    }

    /**
     * @dataProvider testUnsetPaymentErrorsProvider
     */
    public function testUnsetPaymentErrors($iErrorCode, $shouldDeleteOnFail, $shouldDeleteOnCancel, $sExpected)
    {
        $oDb = DatabaseProvider::getDb();
        $oDb->execute("INSERT INTO oxorder (`OXID`, `OXPAYMENTTYPE`) VALUES('oxid1', 'wdpaypal')");
        $oDb->execute(
            "UPDATE oxpayments SET `WDOXIDEE_DELETE_CANCELED_ORDER` = ?, `WDOXIDEE_DELETE_FAILED_ORDER` = ? WHERE `OXID` = 'wdpaypal'",
            [$shouldDeleteOnCancel, $shouldDeleteOnFail]
        );

        Registry::getSession()->setVariable('sess_challenge', 'oxid1');
        $_POST['payerror'] = $iErrorCode;

        $paymentController = new class() extends \Wirecard\Oxid\Extend\Controller\PaymentController
        {
            public function publicUnsetPaymentErrors()
            {
                parent::_unsetPaymentErrors();
            }
        };
        $paymentController->publicUnsetPaymentErrors();

        $sResult = $oDb->getOne("SELECT count(*) from oxorder WHERE `oxid`='oxid1'");
        $this->assertEquals($sExpected, $sResult);
    }

    public function testUnsetPaymentErrorsProvider()
    {
        return [
            'state CANCELLED not deleted' => [\Wirecard\Oxid\Extend\Controller\PaymentController::ERROR_CODE_CANCELED, false, false, '1'],
            'state CANCELLED deleted' => [\Wirecard\Oxid\Extend\Controller\PaymentController::ERROR_CODE_CANCELED, false, true, '0'],
            'state FAILED not deleted' => [\Wirecard\Oxid\Extend\Controller\PaymentController::ERROR_CODE_FAILED, false, false, '1'],
            'state FAILED deleted' => [\Wirecard\Oxid\Extend\Controller\PaymentController::ERROR_CODE_FAILED, true, false, '0'],
        ];
    }
}
