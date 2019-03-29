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

    public function testIsWirecardPaymentMethod()
    {
        $this->assertTrue(Helper::isWirecardPaymentMethod("wdpaypal"));
        $this->assertFalse(Helper::isWirecardPaymentMethod("paypal"));
    }

    public function testGetPaymentsReturnsAssociativeArray()
    {
        $this->assertContainsOnly('string', array_keys(Helper::getPayments()));
    }

    public function testGetPaymentsReturnsPayments()
    {
        $this->assertContainsOnlyInstancesOf(Payment::class, Helper::getPayments());
    }

    public function testGetPluginPayments()
    {
        foreach (Helper::getPluginPayments() as $key => $payment) {
            $this->assertTrue(!!$payment->oxpayments__wdoxidee_iswirecard->value);
        }
    }
}
