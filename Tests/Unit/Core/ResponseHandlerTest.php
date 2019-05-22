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
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Response\SuccessResponse;

class ResponseHandlerTest extends \Wirecard\Test\WdUnitTestCase
{

    public function testOnSuccessResponse()
    {

        $oResponseStub = $this->getMockBuilder(SuccessResponse::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentTransactionId', 'getRequestedAmount', 'getRawData', 'getData'])
            ->getMock();

        $oResponseStub->method('getParentTransactionId')
            ->willReturn('id');

        $oResponseStub->method('getRequestedAmount')
            ->willReturn(new Amount(30, "EUR"));

        $oResponseStub->method('getRawData')
            ->willReturn('<?xml version="1.0" encoding="UTF-8"?>');

        $oResponseStub->method('getData')
            ->willReturn([]);

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
