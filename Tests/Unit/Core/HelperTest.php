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

use OxidEsales\Eshop\Application\Model\Payment;

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
            'module key' => ['config_descriptor', 'Deskriptor'],
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
        $this->assertContainsOnly('string', array_keys(Helper::getPayments()));
    }

    public function testGetPaymentsReturnsPayments()
    {
        $this->assertContainsOnlyInstancesOf(Payment::class, Helper::getPayments());
    }

    public function testGetModulePayments()
    {
        foreach (Helper::getModulePayments() as $key => $payment) {
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
}
