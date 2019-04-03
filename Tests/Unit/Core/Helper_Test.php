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

class Helper_Test extends OxidEsales\TestingLibrary\UnitTestCase
{
    public function testCreateDeviceId()
    {
        $expected = 'test-maid_123456789';
        $actual = Helper::createDeviceFingerprint('test-maid', '123456789');
        $this->assertEquals($expected, $actual);
    }

    public function testIsModulePaymentMethod()
    {
        $this->assertTrue(Helper::isModulePaymentMethod("wdpaypal"));
        $this->assertFalse(Helper::isModulePaymentMethod("paypal"));
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
            $this->assertTrue(!!$payment->oxpayments__wdoxidee_isours->value);
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
}
