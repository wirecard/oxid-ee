<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Core\ResponseMapper;

class ResponseMapperTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var ResponseMapper
     */
    private $_oResponseMapper;

    protected function setUp()
    {
        $sXml = file_get_contents(dirname(__FILE__) . '/../../resources/success_response.xml');
        $this->_oResponseMapper = new ResponseMapper($sXml);

        parent::setUp();
    }

    public function testGetPaymentDetails()
    {
        $aResult = $this->_oResponseMapper->getPaymentDetails();
        $this->assertCount(4, $aResult);
        $this->assertEquals('payment method', $aResult['paymentMethod']);
        $this->assertEquals('2019-04-16T11:38:38.000Z', $aResult['timeStamp']);
        $this->assertEquals('127.0.0.1', $aResult['ip']);
        $this->assertEquals('1', $aResult['orderNumber']);
    }

    public function testGetTransactionDetails()
    {
        $aResult = $this->_oResponseMapper->getTransactionDetails();
        $this->assertCount(7, $aResult);
        $this->assertEquals('Merchand Account ID', $aResult['maid']);
        $this->assertEquals('Transaction ID', $aResult['transactionID']);
        $this->assertEquals('Request ID', $aResult['requestId']);
        $this->assertEquals('debit', $aResult['transactionType']);
        $this->assertEquals('success', $aResult['transactionState']);
        $this->assertEquals('100.000000', $aResult['requestedAmount']);
        $this->assertEquals('Descriptor', $aResult['descriptor']);
    }

    public function testGetAccountHolder()
    {
        $aResult = $this->_oResponseMapper->getAccountHolder();
        $this->assertCount(8, $aResult);
        $this->assertEquals('LastName', $aResult['last-name']);
        $this->assertEquals('FirstName', $aResult['first-name']);
        $this->assertEquals('me@home.at', $aResult['email']);
        $this->assertEquals('+123456789', $aResult['phone']);
        $this->assertEquals('Street 1', $aResult['street1']);
        $this->assertEquals('City', $aResult['city']);
        $this->assertEquals('CC', $aResult['country']);
        $this->assertEquals('90210', $aResult['postal-code']);

    }

    public function testGetShipping()
    {
        $aResult = $this->_oResponseMapper->getShipping();
        $this->assertCount(7, $aResult);
        $this->assertEquals('Shipping Lastname', $aResult['last-name']);
        $this->assertEquals('Shipping Name', $aResult['first-name']);
        $this->assertEquals('+123456789', $aResult['phone']);
        $this->assertEquals('Street 1', $aResult['street1']);
        $this->assertEquals('City', $aResult['city']);
        $this->assertEquals('CC', $aResult['country']);
        $this->assertEquals('90210', $aResult['postal-code']);
    }

    public function testGetBasket()
    {
        $aResult = $this->_oResponseMapper->getBasket();
        $this->assertCount(4, $aResult);
        $this->assertEquals('1', $aResult['quantity']);
        $this->assertEquals('EUR 100', $aResult['amount']);
        $this->assertEquals('Desc 1', $aResult['description']);
        $this->assertEquals('Article Number', $aResult['article-number']);

    }

    public function testGetData()
    {
        $aResult = $this->_oResponseMapper->getData();
        $this->assertCount(69, $aResult);
        $aSubSet = [
            'custom-fields.0.field-name' => 'paysdk_pluginVersion',
            'custom-fields.0.field-value' => '0.1.0',
            'transaction-state' => 'success',
            'completion-time-stamp' => '2019-04-16T11:38:38.000Z',
        ];
        $this->assertArraySubset($aSubSet, $aResult);

    }

    public function testGetCard()
    {
        $aResult = $this->_oResponseMapper->getCard();
        $this->assertArrayHasKey('Masked Pan', $aResult);
    }
}
