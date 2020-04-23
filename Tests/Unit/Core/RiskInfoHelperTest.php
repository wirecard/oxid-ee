<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Core\RiskInfoHelper;
use Wirecard\PaymentSdk\Constant\RiskInfoReorder;
use \Wirecard\PaymentSdk\Constant\RiskInfoDeliveryTimeFrame;

class RiskInfoHelperTest extends OxidEsales\TestingLibrary\UnitTestCase
{

    public function testCreate()
    {
        $oAccountInfo = RiskInfoHelper::create('foo@bar.com', false, false);

        $this->assertEquals(
            [
                'delivery-mail' => 'foo@bar.com',
                'reorder-items' => RiskInfoReorder::FIRST_TIME_ORDERED
            ], $oAccountInfo->mappedProperties()
        );
    }

    public function testCreateReorderedVirtualItems()
    {
        $oAccountInfo = RiskInfoHelper::create('foo@bar.com', true, true);

        $this->assertEquals(
            [
                'delivery-mail'      => 'foo@bar.com',
                'reorder-items'      => RiskInfoReorder::REORDERED,
                'delivery-timeframe' => RiskInfoDeliveryTimeFrame::ELECTRONIC_DELIVERY
            ], $oAccountInfo->mappedProperties()
        );
    }
}
