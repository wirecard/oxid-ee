<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Extend\Controller\Admin\PaymentMain;
use Wirecard\PaymentSdk\TransactionService;

class PaymentMainTest extends \Wirecard\Test\WdUnitTestCase
{

    /**
     * @var PaymentMain
     */
    private $_controller;

    protected function setUp()
    {
        $this->_controller = oxNew(PaymentMain::class);
        parent::setUp();
    }

    /**
     * @dataProvider renderProvider
     */
    public function testRender($bValidationResult, $bExpected)
    {
        $this->_controller->setEditObjectId('wdpaypal');
        $this->setRequestParameter('fnc', 'save');
        if ($bValidationResult) {
            $this->setRequestParameter(
                'editval',
                [
                    'oxpayments__wdoxidee_apiurl' => 'http://api.url',
                    'oxpayments__wdoxidee_httpuser' => 'user',
                    'oxpayments__wdoxidee_httppass' => 'mysecretpasswordnooneknows',
                ]
            );
        }

        $oTransactionServStub = $this->getMockBuilder(TransactionService::class)
            ->disableOriginalConstructor()
            ->setMethods(['checkCredentials'])
            ->getMock();

        $oTransactionServStub->method('checkCredentials')
            ->willReturn($bValidationResult);

        $this->_controller->setTransactionService($oTransactionServStub);
        $this->_controller->render();

        $this->assertEquals($bExpected, $this->_controller->getViewData()['bConfigNotValid']);
    }

    public function renderProvider()
    {
        return [
            'validation success' => [true, true],
            'validation failure' => [false, true],
        ];
    }

    public function testSave()
    {
        $this->setRequestParameter(
            'editval',
            [
                'oxpayments__allowed_currencies' => [],
            ]
        );

        try {
            $this->_controller->save();
        } catch (\Exception $exc) {
            $this->fail($exc->getMessage());
        }
    }

    public function testSaveWithCurrencies()
    {
        $this->_controller->setEditObjectId('wdpayolution-inv');

        $this->setRequestParameter(
            'editval',
            [
                'oxpayments__allowed_currencies' => ['EUR', 'CHF'],
                'oxpayments__httpuser_eur' => 'abcd',
                'oxpayments__httppass_eur' => 'efgh',
                'oxpayments__wdoxidee_apiurl' => 'http://api.url',
                'oxpayments__wdoxidee_httpuser' => 'user',
                'oxpayments__wdoxidee_httppass' => 'mysecretpasswordnooneknows',
            ]
        );

        $oTransactionServStub = $this->getMockBuilder(TransactionService::class)
            ->disableOriginalConstructor()
            ->setMethods(['checkCredentials'])
            ->getMock();

        $oTransactionServStub->method('checkCredentials')
            ->willReturn(true);

        $this->_controller->setTransactionService($oTransactionServStub);

        $this->_controller->save();
    }

    /**
     * @dataProvider creditorIdValidationProvider
     */
    public function testCreditorIdValidation($bResult, $sCreditorId)
    {
        // use an anonymous class to get access to protected methods and variables
        $cPaymentMain = $this->_createPaymentMainClassInstance();

        $bCreditorIdValid = $cPaymentMain->creditorIdValidation($sCreditorId);
        $this->assertEquals($bCreditorIdValid, $bResult);
    }

    public function creditorIdValidationProvider()
    {
        return [
            'creditor id valid' => [1, 'DE08700901001234567890'],
            'creditor id invalid' => [0, 'DE08700914123054890'],
            'creditor id too long' => [0, 'DE98ZZZ09995999290000000000000000000'],
        ];
    }

    private function _createPaymentMainClassInstance()
    {
        $cPaymentMain = new class() extends PaymentMain
        {
            public function creditorIdValidation($sCreditorId)
            {
                return parent::_creditorIdValidation($sCreditorId);
            }
        };
        return $cPaymentMain;
    }

    public function testCheckCurrencyConfigFields()
    {
        $sPaymentId = 'wdpayolution-inv';
        $this->_controller->setEditObjectId($sPaymentId);

        $this->setRequestParameter(
            'editval',
            [
                'oxpayments__oxid' => $sPaymentId,
                'oxpayments__allowed_currencies' => ['EUR', 'GBP'],
                'oxpayments__httpuser_eur' => 'test',
                'oxpayments__httppass_eur' => 'test',
            ]
        );

        try {
            $this->_controller->save();
        } catch (\Exception $exc) {
            $this->fail($exc->getMessage());
        }
    }
}
