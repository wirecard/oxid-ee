<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Core\PostProcessingHelper;
use Wirecard\Oxid\Model\Transaction;

class PostProcessingHelperTest extends \Wirecard\Test\WdUnitTestCase
{
    /**
     * @dataProvider shouldUseOrderItemsProvider
     */
    public function testShouldUseOrderItems($oTransactionStub, $expected)
    {
        $result = PostProcessingHelper::shouldUseOrderItems($oTransactionStub);
        $this->assertEquals($expected, $result);
    }

    public function shouldUseOrderItemsProvider()
    {
        $oPpTransactionStub = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $oPpTransactionStub->method('getPaymentType')
            ->willReturn('wdpaypal');

        $oRpTransactionStub = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $oRpTransactionStub->method('getPaymentType')
            ->willReturn('wdratepay-invoice');

        $oPayTransactionStub = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $oPayTransactionStub->method('getPaymentType')
            ->willReturn('wdpayolution-inv');

        return [
            'PayPal transaction' => [$oPpTransactionStub, false],
            'Ratepay transaction' => [$oRpTransactionStub, true],
            'Payolution transaction' => [$oPayTransactionStub, true],
        ];
    }

    public function testFilterPostProcessingActions()
    {
        $aPossibleOperations = [
            'action1' => 'display1',
            'action2' => 'display2',
        ];

        $oPayment = oxNew(\Wirecard\Oxid\Extend\Model\Payment::class);
        $oPayment->load('wdpaypal');
        $oPayment->oxpayments__oxactive = new \OxidEsales\Eshop\Core\Field(1);
        $oPayment->save();

        $oTransactionStub = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result = PostProcessingHelper::filterPostProcessingActions(
            $aPossibleOperations,
            new \Wirecard\Oxid\Model\PaypalPaymentMethod()
            , $oTransactionStub);

        $this->assertEquals($aPossibleOperations, $result);
    }

    public function testGetMappedTableOrderItems()
    {
        $oTransactionStub = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBasket', 'getChildTransactions'])
            ->getMock();

        $oBasketStub = $this->getMockBuilder(\Wirecard\Oxid\Extend\Model\Basket::class)
            ->disableOriginalConstructor()
            ->setMethods(['mappedProperties'])
            ->getMock();

        $article1Quantity = 1;
        $article2QuantityBefore = 2;

        $oBasketStub->method('mappedProperties')
            ->willReturn([
                'order-item' => [
                    [
                        'article-number' => 'article1',
                        'quantity' => $article1Quantity,
                        'name' => 'name1',
                        'amount' => [
                            'value' => 12.90
                        ],
                    ],
                    [
                        'article-number' => 'article2',
                        'quantity' => $article2QuantityBefore,
                        'name' => 'name2',
                        'amount' => [
                            'value' => 34.70
                        ],
                    ],

                ]
            ]);

        $oTransactionStub->method('getBasket')
            ->willReturn($oBasketStub);

        $oChildTransactionStub = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBasket'])
            ->getMock();

        $oChildBasketStub = $this->getMockBuilder(\Wirecard\Oxid\Extend\Model\Basket::class)
            ->disableOriginalConstructor()
            ->setMethods(['mappedProperties'])
            ->getMock();

        $article2QuantityAfter = 1;

        $oChildBasketStub->method('mappedProperties')
            ->willReturn([
                'order-item' => [
                    [
                        'article-number' => 'article2',
                        'quantity' => $article2QuantityAfter,
                        'name' => 'name2',
                        'amount' => [
                            'value' => 34.70
                        ],
                    ],

                ]
            ]);

        $oChildTransactionStub->method('getBasket')
            ->willReturn($oChildBasketStub);

        $oTransactionStub->method('getChildTransactions')
            ->willReturn([$oChildTransactionStub]);

        $mappedTableOrderItems = PostProcessingHelper::getMappedTableOrderItems($oTransactionStub);

        $expected = [
            [
                ['text' => 'article1'],
                ['text' => 'name1'],
                ['text' => 12.9],
                ['text' => '<input type="number" value="' . $article1Quantity . '" name="quantity[]" min="0" max="' . $article1Quantity . '"/><input type="hidden" value="article1" name="article-number[]" />'],
            ],
            [
                ['text' => 'article2'],
                ['text' => 'name2'],
                ['text' => 34.7],
                ['text' => '<input type="number" value="' . ($article2QuantityBefore - $article2QuantityAfter) . '" name="quantity[]" min="0" max="' . ($article2QuantityBefore - $article2QuantityAfter) . '"/><input type="hidden" value="article2" name="article-number[]" />'],
            ],
        ];
        $this->assertEquals($expected, $mappedTableOrderItems);
    }
}
