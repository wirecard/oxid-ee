<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

require_once dirname(__FILE__) . "/../../../bootstrap.php";

use \OxidEsales\Eshop\Core\Registry;

/**
 * Handles all incomingnotifications coming from paymentSDK
 */
class Notification_Handler extends Base
{
    /**
     * Processes the content of the notification
     */
    public function handle()
    {

        /**
         * var $oLogger \Psr\Log\LoggerInterface
         */
        $oLogger = Registry::getLogger();
        $oLogger->info('handling notification');
        //TODO cgrach: implement notification handling
    }
}

$handler = oxNew('Notification_Handler');
$handler->handle();
