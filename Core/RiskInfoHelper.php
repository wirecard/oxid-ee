<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use Wirecard\PaymentSdk\Constant\RiskInfoDeliveryTimeFrame;
use Wirecard\PaymentSdk\Constant\RiskInfoReorder;
use Wirecard\PaymentSdk\Entity\RiskInfo;

/**
 * Helper class to handle risk info for transactions
 *
 * @since 1.3.0
 */
class RiskInfoHelper
{

    /**
     * Creates an AccountHolder object from an array of arguments.
     *
     * @param string $sUserEmail
     * @param bool   $bHasReorderedItems
     * @param bool   $bHasVirtualItems
     *
     * @return RiskInfo
     *
     * @since 1.3.0
     */
    public static function create($sUserEmail, $bHasReorderedItems, $bHasVirtualItems)
    {
        $oRiskInfo = new RiskInfo();
        $oRiskInfo->setDeliveryEmailAddress($sUserEmail);
        $oRiskInfo->setReorderItems(
            $bHasReorderedItems ?
                RiskInfoReorder::REORDERED : RiskInfoReorder::FIRST_TIME_ORDERED
        );
        if ($bHasVirtualItems) {
            $oRiskInfo->setDeliveryTimeFrame(RiskInfoDeliveryTimeFrame::ELECTRONIC_DELIVERY);
        }

        return $oRiskInfo;
    }
}
