<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use Wirecard\Oxid\Core\OxidEEEvents;

class OxidEEEventsTest extends OxidEsales\TestingLibrary\UnitTestCase
{

    private $_oXmlPaymentMethods;

    private $_aExtendedPaymentTableCols;
    private $_aExtendedOrderTableCols;

    /**
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    protected function setUp()
    {
        DatabaseProvider::getDB()->execute("DROP TABLE IF EXISTS `wdoxidee_ordertransactions`");

        $this->_oXmlPaymentMethods = simplexml_load_file(dirname(__FILE__) . "/../../../default_payment_config.xml");

        foreach ($this->_oXmlPaymentMethods->payment as $paymentMethod) {
            $payment = oxNew(Payment::class);
            $payment->load($paymentMethod->oxid);
            $payment->delete();
        }

        $this->_aExtendedPaymentTableCols = [
            'WDOXIDEE_LABEL',
            'WDOXIDEE_LOGO',
            'WDOXIDEE_TRANSACTIONACTION',
            'WDOXIDEE_APIURL',
            'WDOXIDEE_MAID',
            'WDOXIDEE_ISOURS',
            'WDOXIDEE_SECRET',
            'WDOXIDEE_THREE_D_MAID',
            'WDOXIDEE_THREE_D_SECRET',
            'WDOXIDEE_NON_THREE_D_MAX_LIMIT',
            'WDOXIDEE_THREE_D_MIN_LIMIT',
            'WDOXIDEE_LIMITS_CURRENCY',
            'WDOXIDEE_HTTPUSER',
            'WDOXIDEE_HTTPPASS',
            'WDOXIDEE_BASKET',
            'WDOXIDEE_DESCRIPTOR',
            'WDOXIDEE_ADDITIONAL_INFO',
        ];

        foreach ($this->_aExtendedPaymentTableCols as $colName) {
            DatabaseProvider::getDb()->execute("ALTER TABLE `oxpayments` DROP COLUMN `{$colName}`");
        }

        $this->_aExtendedOrderTableCols = [
            'WDOXIDEE_ORDERSTATE',
            'WDOXIDEE_FINAL',
            'WDOXIDEE_PROVIDERTRANSACTIONID',
            'WDOXIDEE_TRANSACTIONID',
            'WDOXIDEE_FINALIZEORDERSTATE',
        ];

        foreach ($this->_aExtendedOrderTableCols as $colName) {
            DatabaseProvider::getDb()->execute("ALTER TABLE `oxorder` DROP COLUMN `{$colName}`");
        }

        parent::setUp();
    }

    /**
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    protected function tearDown()
    {
        DatabaseProvider::getDB()->execute("DROP TABLE IF EXISTS `" . \Wirecard\Oxid\Core\OxidEEEvents::TRANSACTION_TABLE . "`");
        OxidEEEvents::createOrderTransactionTable();

        OxidEEEvents::extendPaymentMethodTable();
        OxidEEEvents::extendOrderTable();
        OxidEEEvents::addPaymentMethods();

        parent::tearDown();
    }

    public function testOnActivate()
    {
        OxidEEEvents::onActivate();

        $dbMetaDataHandler = oxNew(DbMetaDataHandler::class);
        $this->assertTrue($dbMetaDataHandler->tableExists('wdoxidee_ordertransactions'));

        foreach ($this->_oXmlPaymentMethods->payment as $paymentMethod) {
            $payment = oxNew(Payment::class);
            $payment->load($paymentMethod->oxid);
            $this->assertEquals(0, $payment->oxpayments__oxactive->value, $paymentMethod->oxid . " is active");
        }

        foreach ($this->_aExtendedPaymentTableCols as $colName) {
            $this->assertTrue($dbMetaDataHandler->fieldExists($colName, 'oxpayments'));
        }

        foreach ($this->_aExtendedOrderTableCols as $colName) {
            $this->assertTrue($dbMetaDataHandler->fieldExists($colName, 'oxorder'));
        }
    }

    public function testOnDeactivate()
    {
        OxidEEEvents::onActivate();
        OxidEEEvents::onDeactivate();

        $dbMetaDataHandler = oxNew(DbMetaDataHandler::class);
        $this->assertTrue($dbMetaDataHandler->tableExists('wdoxidee_ordertransactions'));

        foreach ($this->_oXmlPaymentMethods->payment as $paymentMethod) {
            $payment = oxNew(Payment::class);
            $payment->load($paymentMethod->oxid);
            $this->assertEquals(0, $payment->oxpayments__oxactive->value, $paymentMethod->oxid . " is active");
        }

        foreach ($this->_aExtendedPaymentTableCols as $colName) {
            $this->assertTrue($dbMetaDataHandler->fieldExists($colName, 'oxpayments'));
        }

        foreach ($this->_aExtendedOrderTableCols as $colName) {
            $this->assertTrue($dbMetaDataHandler->fieldExists($colName, 'oxorder'));
        }
    }
}
