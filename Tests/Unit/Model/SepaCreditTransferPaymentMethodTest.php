<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Core\Field;
use Wirecard\Oxid\Model\SepaCreditTransferPaymentMethod;
use Wirecard\PaymentSdk\Transaction\SepaCreditTransferTransaction;

class SepaCreditTransferPaymentMethodTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var SepaCreditTransferPaymentMethod
     */
    private $_oPaymentMethod;

    protected function setUp()
    {
        $extendedPaymentMethod = new class() extends SepaCreditTransferPaymentMethod
        {
            public function getExtendedTransaction()
            {
                $extendedTransaction = new class() extends SepaCreditTransferTransaction
                {
                    public function publicMappedSpecificProperties()
                    {
                        return parent::mappedSpecificProperties();
                    }
                };

                return new $extendedTransaction();
            }
        };

        parent::setUp();
        $this->_oPaymentMethod = new $extendedPaymentMethod();
    }

    public function testGetConfig()
    {
        $oConfig = $this->_oPaymentMethod->getConfig();
        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('sepacredit'));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->_oPaymentMethod->getTransaction();
        $this->assertInstanceOf(SepaCreditTransferTransaction::class, $oTransaction);
    }

    public function testGetPublicFieldNames()
    {
        $aPublicFieldNames = $this->_oPaymentMethod->getPublicFieldNames();
        $this->assertCount(4, $aPublicFieldNames);
        $aExpected = [
            "apiUrl",
            "maid",
            "deleteCanceledOrder",
            "deleteFailedOrder",
        ];
        $this->assertEquals($aExpected, $aPublicFieldNames, '', 0.0, 1, true);
    }

    public function testAddNeededDataToTransaction()
    {
        $oParentTransaction = oxNew(Wirecard\Oxid\Model\Transaction::class);
        $oParentTransaction->wdoxidee_ordertransactions__orderid = new Field('veryLongOrderIdSoLongThatItNeedsToBeCut');

        $sSofortResponse = file_get_contents(__DIR__ . '/../../resources/sofort_response.xml');
        $oParentTransaction->wdoxidee_ordertransactions__responsexml = new Field(base64_encode($sSofortResponse));

        $oTransaction = $this->_oPaymentMethod->getExtendedTransaction();

        $aMappedProperties = $oTransaction->getAccountHolder();
        $this->assertNull($aMappedProperties);
        $aMappedProperties = $oTransaction->publicMappedSpecificProperties();
        $this->assertNotEquals('AT850000000023456789', $aMappedProperties['bank-account']['iban']);
        $this->assertNotEquals('SFRTAT20XXX', $aMappedProperties['bank-account']['bic']);

        $this->_oPaymentMethod->addNeededDataToTransaction($oTransaction, $oParentTransaction);

        $aMappedProperties = $oTransaction->getAccountHolder()->mappedProperties();
        $this->assertEquals('Max', $aMappedProperties['first-name']);
        $this->assertEquals('Mustermann', $aMappedProperties['last-name']);
        $aMappedProperties = $oTransaction->publicMappedSpecificProperties();
        $this->assertEquals('AT850000000023456789', $aMappedProperties['bank-account']['iban']);
        $this->assertEquals('SFRTAT20XXX', $aMappedProperties['bank-account']['bic']);
    }

    public function testIsMerchantOnly()
    {
        $this->assertTrue($this->_oPaymentMethod->isMerchantOnly());
    }
}
