<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Application\Model\Order;

use Wirecard\Oxid\Core\ResponseHandler;

use Wirecard\PaymentSdk\BackendService;
use Wirecard\PaymentSdk\Response\SuccessResponse;

class ResponseHandlerTest extends \Wirecard\Test\WdUnitTestCase
{

    public function testOnSuccessResponse()
    {

        $oResponseStub = $this->getMockBuilder(SuccessResponse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oResponseStub->method('getParentTransactionId')
            ->willReturn('');

        $oBackendService = $this->getMockBuilder(BackendService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oOrder = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        try {
            ResponseHandler::onSuccessResponse($oResponseStub, $oBackendService, $oOrder);
        } catch (Exception $exc) {

            $this->fail("testOnSuccessResponse failed because " . get_class($exc) . " was thrown");
        }
    }
}
