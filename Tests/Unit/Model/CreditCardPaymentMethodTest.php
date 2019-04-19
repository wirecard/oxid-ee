<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Application\Model\Payment;
use Wirecard\Oxid\Model\CreditCardPaymentMethod;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\Oxid\Core\PaymentMethodHelper;

class CreditCardPaymentMethodTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * @var CreditCardPaymentMethod
     */
    private $oPaymentMethod;

    protected function setUp()
    {
        parent::setUp();
        $this->oPaymentMethod = new CreditCardPaymentMethod();
    }

    public function testGetConfig()
    {
        /**
         * @var Payment $oPayment
         */
        $oPayment = PaymentMethodHelper::getPaymentById(CreditCardPaymentMethod::getName(true));

        $oConfig = $this->oPaymentMethod->getConfig($oPayment);
        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('creditcard'));
    }

    public function testGetTransaction()
    {
        $oTransaction = $this->oPaymentMethod->getTransaction();
        $this->assertTrue($oTransaction instanceof CreditCardTransaction);
    }

    public function testGetTransactionNotSettingNon3dCredentials()
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->oxpayments__wdoxidee_three_d_maid = new Field("THREE D MAID");
        $oPayment->oxpayments__wdoxidee_three_d_secret = new Field("THREE D SECRET");
        $oPayment->oxpayments__wdoxidee_non_three_d_max_limit = new Field("");
        $oPayment->oxpayments__wdoxidee_three_d_min_limit = new Field("");

        $oConfig = $this->oPaymentMethod->getConfig($oPayment);

        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('creditcard'));

        /**
         * @var $oCreditCardConfig \Wirecard\PaymentSdk\Config\CreditCardConfig
         */
        $oCreditCardConfig = $oConfig->get('creditcard');

        $this->assertEquals("THREE D MAID", $oCreditCardConfig->getThreeDMerchantAccountId());
        $this->assertEquals("THREE D SECRET", $oCreditCardConfig->getThreeDSecret());
        $this->assertNull($oCreditCardConfig->getMerchantAccountId());
        $this->assertNull($oCreditCardConfig->getSecret());
    }

    public function testGetTransactionNotSetting3dCredentials()
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->oxpayments__wdoxidee_maid = new Field("MAID");
        $oPayment->oxpayments__wdoxidee_secret = new Field("SECRET");
        $oPayment->oxpayments__wdoxidee_non_three_d_max_limit = new Field("");
        $oPayment->oxpayments__wdoxidee_three_d_min_limit = new Field("");

        $oConfig = $this->oPaymentMethod->getConfig($oPayment);

        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('creditcard'));

        /**
         * @var $oCreditCardConfig \Wirecard\PaymentSdk\Config\CreditCardConfig
         */
        $oCreditCardConfig = $oConfig->get('creditcard');

        $this->assertEquals("MAID", $oCreditCardConfig->getMerchantAccountId());
        $this->assertEquals("SECRET", $oCreditCardConfig->getSecret());
        $this->assertNull($oCreditCardConfig->getThreeDMerchantAccountId());
        $this->assertNull($oCreditCardConfig->getThreeDSecret());
    }

    public function testGetTransactionSettingNon3dMaxLimit()
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->oxpayments__wdoxidee_non_three_d_max_limit = new Field(300);
        $oPayment->oxpayments__wdoxidee_limits_currency = new Field('EUR');
        $oPayment->oxpayments__wdoxidee_three_d_min_limit = new Field('');

        $oConfig = $this->oPaymentMethod->getConfig($oPayment);

        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('creditcard'));

        /**
         * @var $oCreditCardConfig \Wirecard\PaymentSdk\Config\CreditCardConfig
         */
        $oCreditCardConfig = $oConfig->get('creditcard');

        $this->assertEquals(300, $oCreditCardConfig->getNonThreeDMaxLimit("EUR"));
        $this->assertEmpty($oCreditCardConfig->getThreeDMinLimit("EUR"));
    }

    public function testGetTransactionSetting3dMinLimit()
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->oxpayments__wdoxidee_three_d_min_limit = new Field(300);
        $oPayment->oxpayments__wdoxidee_limits_currency = new Field('EUR');
        $oPayment->oxpayments__wdoxidee_non_three_d_max_limit = new Field('');

        $oConfig = $this->oPaymentMethod->getConfig($oPayment);

        $this->assertNotNull($oConfig);
        $this->assertNotNull($oConfig->get('creditcard'));

        /**
         * @var $oCreditCardConfig \Wirecard\PaymentSdk\Config\CreditCardConfig
         */
        $oCreditCardConfig = $oConfig->get('creditcard');

        $this->assertEquals(300, $oCreditCardConfig->getThreeDMinLimit("EUR"));
        $this->assertEmpty($oCreditCardConfig->getNonThreeDMaxLimit("EUR"));
    }

    public function testGetConfigFields() {
        $aConfigFields = $this->oPaymentMethod->getConfigFields();
        $this->assertArrayHasKey("threeDMaid", $aConfigFields);
        $this->assertArrayHasKey("threeDSecret", $aConfigFields);
        $this->assertArrayHasKey("nonThreeDMaxLimit", $aConfigFields);
        $this->assertArrayHasKey("threeDMinLimit", $aConfigFields);
        $this->assertArrayHasKey("limitsCurrency", $aConfigFields);
    }
}
