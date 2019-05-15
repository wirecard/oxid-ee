<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 *
 */

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Core\PaymentMethodHelper;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Module\Module;

class HelperTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @dataProvider testTranslateProvider
     */
    public function testTranslate($input, $expected)
    {
        $this->assertEquals(Helper::translate($input), $expected);
    }

    public function testTranslateProvider()
    {
        return [
            'OXID key' => ['WRAPPING', 'Verpackung'],
            'module key' => ['wd_config_descriptor', 'Deskriptor'],
            'unknown key' => ['foo', 'foo'],
        ];
    }

    public function testCreateDeviceId()
    {
        $expected = 'test-maid_123456789';
        $actual = Helper::createDeviceFingerprint('test-maid', '123456789');
        $this->assertEquals($expected, $actual);
    }

    public function testGetPaymentsReturnsAssociativeArray()
    {
        $this->assertContainsOnly('string', array_keys(PaymentMethodHelper::getPayments()));
    }

    public function testGetPaymentsReturnsPayments()
    {
        $this->assertContainsOnlyInstancesOf(Payment::class, PaymentMethodHelper::getPayments());
    }

    public function testGetModulePayments()
    {
        foreach (PaymentMethodHelper::getModulePayments() as $key => $payment) {
            $this->assertTrue($payment->isCustomPaymentMethod());
        }
    }

    /**
     * @dataProvider testGetFloatFromStringProvider
     */
    public function testGetFloatFromString($input, $expected)
    {
        $this->assertEquals(Helper::getFloatFromString($input), $expected);
    }

    public function testGetFloatFromStringProvider()
    {
        return [
            'decimals English' => ['1.234', 1.234],
            'decimals German' => ['1,234', 1.234],
            'thousands English' => ['1,234.00', 1234],
            'thousands German' => ['1.234,00', 1234],
            'millions English' => ['1,234,567.00', 1234567],
            'millions German' => ['1.234.567,00', 1234567],
            'mixed English' => ['1,234,567.89', 1234567.89],
            'mixed German' => ['1.234.567,89', 1234567.89],
        ];
    }

    /**
     * @dataProvider testGetGenderCodeForSalutationProvider
     */
    public function testGetGenderCodeForSalutation($input, $expected)
    {
        $this->assertEquals(Helper::getGenderCodeForSalutation($input), $expected);
    }

    public function testGetGenderCodeForSalutationProvider()
    {
        return [
            'male' => ['MR', 'm'],
            'female' => ['MRS', 'f'],
            'unknown' => ['FOO', ''],
        ];
    }

    /**
     * @dataProvider testGetDateTimeFromStringProvider
     */
    public function testGetDateTimeFromString($input, $expected)
    {
        $this->assertEquals(Helper::getDateTimeFromString($input), $expected);
    }

    public function testGetDateTimeFromStringProvider()
    {
        return [
            'date' => ['2010-10-10', new DateTime('2010-10-10')],
            'date and time' => ['2010-10-10Z10:10:10', new DateTime('2010-10-10Z10:10:10')],
            'invalid date' => ['2010-1000-1000', null],
            'zero date' => ['0000-00-00', null],
            'no date' => ['foo', null],
        ];
    }

    /**
     * @dataProvider testGetFormattedDbDateProvider
     */
    public function testGetFormattedDbDate($input, $expected)
    {
        $this->assertContains($expected, Helper::getFormattedDbDate($input));
    }

    public function testGetFormattedDbDateProvider()
    {
        return [
            'date time format' => [
                '2010-10-10T13:10:10.000Z',
                date('Y-m-d H:i:s', strtotime('2010-10-10T13:10:10.000Z'))
            ],
            'date time format now' => [null, date('Y-m-d')],
        ];
    }

    public function testIsEmailValid()
    {
        $this->assertTrue(Helper::isEmailValid('test@test.com'));
        $this->assertFalse(Helper::isEmailValid('test'));
        $this->assertFalse(Helper::isEmailValid(''));
    }

    public function testGetPaymentsIncludingInactive()
    {
        $aPaymentsList = Helper::getPaymentsIncludingInactive();
        $this->assertNotEmpty($aPaymentsList);
        $this->assertContainsOnly(Payment::class, $aPaymentsList);
    }

    public function testGetModulePaymentsIncludingInactive()
    {
        $aPaymentsList = Helper::getModulePaymentsIncludingInactive();
        $this->assertNotEmpty($aPaymentsList);
        $this->assertContainsOnly(Payment::class, $aPaymentsList);
    }

    public function testGetModulesList()
    {
        $aModuleList = Helper::getModulesList();
        $this->assertContainsOnly(Module::class, $aModuleList);
    }

    public function testIsThisModule()
    {
        $this->assertTrue(Helper::isThisModule('wdoxidee'));
        $this->assertFalse(Helper::isThisModule('test'));
    }
}
