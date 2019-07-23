<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Extend\Model\Order;
use Wirecard\Oxid\Model\PaymentMethod\SepaDirectDebitPaymentMethod;
use Wirecard\Oxid\Model\Transaction;

/**
 * Class handles module behaviour on shop installation events
 *
 * @since 1.0.0
 */
class OxidEeEvents
{
    const OBJECT_PAYMENT_TABLE = "oxobject2payment";
    const ORDER_TABLE = "oxorder";
    const PAYMENT_TABLE = "oxpayments";
    const TRANSACTION_TABLE = "wdoxidee_ordertransactions";
    const PAYMENT_METADATA_TABLE = "wdoxidee_oxpayments_metadata";
    const VAULT_TABLE = "wdoxidee_vault";

    /**
     * @var DatabaseInterface
     *
     * @since 1.0.0
     */
    private static $_oDb;

    /**
     * Extends OXID's internal payment methods table with the fields required by the module
     *
     * @since 1.0.0
     */
    public static function extendPaymentMethodTable()
    {
        $aColumnSettings = [
            'WDOXIDEE_LABEL' => "varchar(128) default '' NOT NULL",
            'WDOXIDEE_LOGO' => "varchar(256) default '' NOT NULL",
            'WDOXIDEE_TRANSACTIONACTION' => "enum('" . implode("','", Transaction::getActions()) . "') NOT NULL",
            'WDOXIDEE_APIURL' => "varchar(128) default '' NOT NULL",
            'WDOXIDEE_MAID' => "varchar(128) default '' NOT NULL",
            'WDOXIDEE_ISOURS' => "tinyint(1) default 0 NOT NULL",
            'WDOXIDEE_SECRET' => "varchar(128) default '' NOT NULL",
            'WDOXIDEE_THREE_D_MAID' => "varchar(128) default '' NOT NULL",
            'WDOXIDEE_THREE_D_SECRET' => "varchar(128) default '' NOT NULL",
            'WDOXIDEE_NON_THREE_D_MAX_LIMIT' => "varchar(128) default '' NOT NULL",
            'WDOXIDEE_THREE_D_MIN_LIMIT' => "varchar(128) default '' NOT NULL",
            'WDOXIDEE_LIMITS_CURRENCY' => "varchar(128) default '' NOT NULL",
            'WDOXIDEE_HTTPUSER' => "varchar(128) default '' NOT NULL",
            'WDOXIDEE_HTTPPASS' => "varchar(128) default '' NOT NULL",
            'WDOXIDEE_BASKET' => "tinyint(1) default 0 NOT NULL",
            'WDOXIDEE_DESCRIPTOR' => "tinyint(1) default 0 NOT NULL",
            'WDOXIDEE_ADDITIONAL_INFO' => "tinyint(1) default 0 NOT NULL",
            'WDOXIDEE_DELETE_CANCELED_ORDER' => "tinyint(1) default 0 NOT NULL",
            'WDOXIDEE_DELETE_FAILED_ORDER' => "tinyint(1) default 0 NOT NULL",
            'WDOXIDEE_COUNTRYCODE' => "varchar(5) default '' NOT NULL",
            'WDOXIDEE_LOGOVARIANT' => "enum('standard', 'descriptive')",
            'WDOXIDEE_BIC' => "tinyint(1) default 0 NOT NULL",
            'WDOXIDEE_CREDITORID' => "varchar(35) default '' NOT NULL",
            'WDOXIDEE_SEPAMANDATECUSTOM' => "text default '' NOT NULL",
            'WDOXIDEE_SEPAMANDATECUSTOM_1' => "text default '' NOT NULL",
            'WDOXIDEE_SEPAMANDATECUSTOM_2' => "text default '' NOT NULL",
            'WDOXIDEE_SEPAMANDATECUSTOM_3' => "text default '' NOT NULL",
        ];

        foreach ($aColumnSettings as $sColumnName => $sSetting) {
            DatabaseHelper::addColumnIfNotExists(
                self::PAYMENT_TABLE,
                $sColumnName,
                "ALTER TABLE `" . self::PAYMENT_TABLE . "` ADD COLUMN `{$sColumnName}` {$sSetting}"
            );
        }
    }

    /**
     * Extends OXID's internal order table with the fields required by the module
     *
     * @since 1.0.0
     */
    public static function extendOrderTable()
    {
        $aColumnSettings = [
            'WDOXIDEE_ORDERSTATE' => "enum('" . implode("','", Order::getStates()) . "') default '" .
                Order::getStates()[0] . "' NOT NULL",
            'WDOXIDEE_FINAL' => "tinyint(1) default 0 NOT NULL",
            'WDOXIDEE_PROVIDERTRANSACTIONID' => "varchar(36) NOT NULL",
            'WDOXIDEE_TRANSACTIONID' => "varchar(36) NOT NULL",
            'WDOXIDEE_FINALIZEORDERSTATE' => "int NOT NULL",
            'WDOXIDEE_SEPAMANDATE' => "text",
        ];

        foreach ($aColumnSettings as $sColumnName => $sSetting) {
            DatabaseHelper::addColumnIfNotExists(
                self::ORDER_TABLE,
                $sColumnName,
                "ALTER TABLE `" . self::ORDER_TABLE . "` ADD COLUMN `{$sColumnName}` {$sSetting}"
            );
        }
    }

    /**
     * Creates the module's order transaction table
     *
     * @since 1.0.0
     */
    public static function createOrderTransactionTable()
    {
        $sTransactionActions = implode("','", Transaction::getActions());
        $sTransactionStates = implode("','", Transaction::getStates());

        $sQuery = "CREATE TABLE IF NOT EXISTS " . self::TRANSACTION_TABLE . "(
            `OXID` char(32) NOT NULL,
            `ORDERID` varchar(32) NOT NULL,
            `ORDERNUMBER` int NOT NULL DEFAULT 0,
            `TRANSACTIONID` varchar(36) NOT NULL,
            `PARENTTRANSACTIONID` varchar(36),
            `REQUESTID` varchar(36) NOT NULL,
            `ACTION` enum('{$sTransactionActions}') NOT NULL,
            `TYPE` varchar(32) NOT NULL,
            `STATE` enum('{$sTransactionStates}') NOT NULL,
            `AMOUNT` double NOT NULL,
            `CURRENCY` varchar(32) NOT NULL,
            `RESPONSEXML` mediumtext NOT NULL,
            `DATE` TIMESTAMP NOT NULL,
            `VALIDSIGNATURE` tinyint(1),
            `TRANSACTIONNUMBER` int NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (`TRANSACTIONNUMBER`)
        ) Engine=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

        self::$_oDb->execute($sQuery);
    }

    /**
     * Creates the module's payment method meta data table
     *
     * @since 1.2.0
     */
    public static function createPaymentMethodMetaDataTable()
    {
        $sQuery = "CREATE TABLE IF NOT EXISTS " . self::PAYMENT_METADATA_TABLE . "(
            `OXID` char(32) NOT NULL,
            `OXOBJECTID` char(32) NOT NULL,
            `KEY` varchar(255) NOT NULL,
            `VALUE` TEXT,
            PRIMARY KEY (`OXID`)
        ) Engine=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

        self::$_oDb->execute($sQuery);
    }

    /**
     * Add Wirecard's payment methods defined in payments.xml
     *
     * @return undefined
     *
     * @since 1.0.0
     */
    public static function addPaymentMethods()
    {
        $oLogger = Registry::getLogger();
        $oXmldata = simplexml_load_file(dirname(__DIR__) . "/default_payment_config.xml");

        if (!$oXmldata) {
            $oLogger->error("default_payment_config.xml could not be loaded.");
            return;
        }

        foreach ($oXmldata->payment as $oPayment) {
            self::_addPaymentMethod($oPayment);
        }
    }

    /**
     * Add Wirecard's payment method
     *
     * @param object $oPayment
     *
     *
     * @since 1.0.0
     */
    private static function _addPaymentMethod($oPayment)
    {
        $aKeyValue = [
            "OXID" => $oPayment->oxid,
        ];

        $sOxdescKey = (string) $oPayment->oxdesc['id'];
        $sOxdesc = Helper::translate($sOxdescKey, 0);
        $sOxdesc1 = Helper::translate($sOxdescKey, 1);

        $sQuery = "INSERT INTO " . self::PAYMENT_TABLE . "(`OXID`, `OXACTIVE`, `OXFROMAMOUNT`, `OXTOAMOUNT`, `OXDESC`,
         `OXDESC_1`, `OXSORT`, `WDOXIDEE_LOGO`, `WDOXIDEE_TRANSACTIONACTION`, `WDOXIDEE_APIURL`, `WDOXIDEE_MAID`,
         `WDOXIDEE_SECRET`, `WDOXIDEE_THREE_D_MAID`, `WDOXIDEE_THREE_D_SECRET`, `WDOXIDEE_NON_THREE_D_MAX_LIMIT`,
         `WDOXIDEE_THREE_D_MIN_LIMIT`, `WDOXIDEE_LIMITS_CURRENCY`, `WDOXIDEE_HTTPUSER`, `WDOXIDEE_HTTPPASS`,
         `WDOXIDEE_ISOURS`, `WDOXIDEE_BASKET`, `WDOXIDEE_DESCRIPTOR`, `WDOXIDEE_ADDITIONAL_INFO`,
         `WDOXIDEE_COUNTRYCODE`, `WDOXIDEE_LOGOVARIANT`, `WDOXIDEE_CREDITORID`) VALUES (
             '{$oPayment->oxid}',
             '{$oPayment->oxactive}',
             '{$oPayment->oxfromamount}',
             '{$oPayment->oxtoamount}',
             '{$sOxdesc}',
             '{$sOxdesc1}',
             '{$oPayment->oxsort}',
             '{$oPayment->wdoxidee_logo}',
             '{$oPayment->wdoxidee_transactionaction}',
             '{$oPayment->wdoxidee_apiurl}',
             '{$oPayment->wdoxidee_maid}',
             '{$oPayment->wdoxidee_secret}',
             '{$oPayment->wdoxidee_three_d_maid}',
             '{$oPayment->wdoxidee_three_d_secret}',
             '{$oPayment->wdoxidee_non_three_d_max_limit}',
             '{$oPayment->wdoxidee_three_d_min_limit}',
             '{$oPayment->wdoxidee_limits_currency}',
             '{$oPayment->wdoxidee_httpuser}',
             '{$oPayment->wdoxidee_httppass}',
             '1',
             '{$oPayment->wdoxidee_basket}',
             '{$oPayment->wdoxidee_descriptor}',
             '{$oPayment->wdoxidee_additional_info}',
             '{$oPayment->wdoxidee_countrycode}',
             '{$oPayment->wdoxidee_logovariant}',
             '{$oPayment->wdoxidee_creditorid}'
        );";

        // insert payment method
        $bIsInserted = DatabaseHelper::insertRowIfNotExists(self::PAYMENT_TABLE, $aKeyValue, $sQuery);
        if ((string) $oPayment->oxid === SepaDirectDebitPaymentMethod::getName() && $bIsInserted) {
            self::_insertSepaMandate();
        }

        self::_addPaymentMethodMetaData($oPayment);

        $sRandomOxidId = Registry::getUtilsObject()->generateUID();

        // insert payment method configuration (necessary for making the payment visible in the checkout page)
        DatabaseHelper::insertRowIfNotExists(
            self::OBJECT_PAYMENT_TABLE,
            ['OXPAYMENTID' => $oPayment->oxid],
            "INSERT INTO " . self::OBJECT_PAYMENT_TABLE . " (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) VALUES (
                '{$sRandomOxidId}',
                '{$oPayment->oxid}',
                'oxidstandard',
                'oxdelset'
            );"
        );
    }

    /**
     * Adds the payment method meta data to the database.
     *
     * @param object $oPaymentXml
     *
     * @since 1.2.0
     */
    private static function _addPaymentMethodMetaData($oPaymentXml)
    {
        $sPaymentId = (string) $oPaymentXml->oxid;

        $oPaymentMethod = PaymentMethodFactory::create($sPaymentId);
        $oPayment = $oPaymentMethod->getPayment();

        foreach ($oPaymentMethod->getMetaDataFieldNames() as $sFieldName) {
            if (isset($oPaymentXml->$sFieldName)) {
                $sOxidFieldName = self::PAYMENT_TABLE . '__' . $sFieldName;

                $mParsedFieldValue = Helper::parseXmlNode($oPaymentXml->$sFieldName);
                $oPayment->$sOxidFieldName = new Field($mParsedFieldValue);
            }
        }

        $oPayment->save();
    }

    /**
     * Adds initial SEPA mandate text to SEPA Direct Debit payment method
     *
     * @since 1.1.0
     */
    private static function _insertSepaMandate()
    {
        $sSepaMandate = self::_prepareSepaMandate(0);
        $sSepaMandate1 = self::_prepareSepaMandate(1);
        $sPaymentId = SepaDirectDebitPaymentMethod::getName();
        $sQuery = "UPDATE oxpayments SET `WDOXIDEE_SEPAMANDATECUSTOM` = '$sSepaMandate',
                                         `WDOXIDEE_SEPAMANDATECUSTOM_1` = '$sSepaMandate1'
                   WHERE `OXID` LIKE " . "'" . $sPaymentId . "'";
        self::$_oDb->execute($sQuery);
    }

    /**
     * Prepares SEPA mandate text for the given language id
     *
     * @param integer $iLanguageId
     *
     * @return string
     *
     * @since 1.1.0
     */
    private static function _prepareSepaMandate($iLanguageId)
    {
        return Helper::translate('wd_sepa_text_1', $iLanguageId) . ' %creditorName% ' .
            Helper::translate('wd_sepa_text_2', $iLanguageId) . ' %creditorName% ' .
            Helper::translate('wd_sepa_text_2b', $iLanguageId) . '\n\n' .
            Helper::translate('wd_sepa_text_3', $iLanguageId) . '\n\n' .
            Helper::translate('wd_sepa_text_4', $iLanguageId) . ' %creditorName% ' .
            Helper::translate('wd_sepa_text_5', $iLanguageId);
    }

    /**
     * Handle OXID's onActivate event
     *
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws DatabaseErrorException
     *
     * @since 1.0.0
     */
    public static function onActivate()
    {
        self::$_oDb = DatabaseProvider::getDb();

        // extend OXID's payment method table
        self::extendPaymentMethodTable();

        // extend OXID's order table
        self::extendOrderTable();

        // create the module's own order transaction table
        self::createOrderTransactionTable();

        // needs to be executed before _addPaymentMethods()
        self::_migrateFrom100To110();
        self::_migrateFrom110To120();
        self::_migrateFrom120To130();

        // view tables must be regenerated after modifying database table structure
        Helper::regenerateViews();

        self::addPaymentMethods();

        self::_clearFileCache();
    }

    /**
     * Handle OXID's onDeactivate event
     *
     * @since 1.0.0
     */
    public static function onDeactivate()
    {
        self::$_oDb = DatabaseProvider::getDb();
        self::_disablePaymentMethods();
        self::_clearFileCache();
    }

    /**
     * Clears file cache
     *
     * @since 1.2.0
     */
    private static function _clearFileCache()
    {
        $sTmpDir = getShopBasePath() . "/tmp/";
        $sSmartyDir = $sTmpDir . "smarty/";

        foreach (glob($sTmpDir . "*.txt") as $sFileName) {
            @unlink($sFileName);
        }
        foreach (glob($sSmartyDir . "*.php") as $sFileName) {
            @unlink($sFileName);
        }
    }

    /**
     * Deactivate wirecard payment methods
     *
     * @since 1.0.0
     */
    private static function _disablePaymentMethods()
    {
        $sQuery = "UPDATE oxpayments SET `OXACTIVE` = 0 WHERE `OXID` LIKE 'wd%'";

        self::$_oDb->execute($sQuery);
    }

    /**
     * Create the Vault table to store Credit Card Information
     *
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     *
     * @since 1.3.0
     */
    private static function _createVaultTable()
    {
        $sQuery = "CREATE TABLE IF NOT EXISTS " . self::VAULT_TABLE . "(
            `OXID` int NOT NULL AUTO_INCREMENT,
            `USERID` varchar(32) NOT NULL,
            `ADDRESSID` varchar(32) NOT NULL,
            `TOKEN` varchar(20) NOT NULL,
			`MASKEDPAN` varchar(30) NOT NULL,
			`EXPIRATIONMONTH` int NOT NULL,
			`EXPIRATIONYEAR` int NOT NULL,
            PRIMARY KEY (`OXID`),
            INDEX ids (`USERID`, `ADDRESSID`)
        ) Engine=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

        self::$_oDb->execute($sQuery);
    }

    /**
     * Migration steps from version 1.0.0 to 1.1.0
     *
     * @since 1.1.0
     */
    private static function _migrateFrom100To110()
    {
        $oDbMetaDataHandler = oxNew(DbMetaDataHandler::class);

        if (!$oDbMetaDataHandler->fieldExists('TRANSACTIONNUMBER', self::TRANSACTION_TABLE)) {
            // adds a sortable transaction number
            $sQuery = "ALTER TABLE " . self::TRANSACTION_TABLE .
                " DROP PRIMARY KEY, ADD COLUMN `TRANSACTIONNUMBER` int AUTO_INCREMENT NOT NULL PRIMARY KEY";
            self::$_oDb->execute($sQuery);

            // adds new enum to transactionActions field
            $sTransactionActions = implode("','", Transaction::getActions());

            $sQuery = "ALTER TABLE " . self::TRANSACTION_TABLE .
                " MODIFY `ACTION` enum('{$sTransactionActions}')";
            self::$_oDb->execute($sQuery);

            $sQuery = "ALTER TABLE " . self::PAYMENT_TABLE .
                " MODIFY COLUMN `WDOXIDEE_TRANSACTIONACTION` enum('{$sTransactionActions}')";
            self::$_oDb->execute($sQuery);
        }

        // adds a unique index on the transaction ID to preemptively prevent multiple entries of the same transaction
        if (!$oDbMetaDataHandler->hasIndex('TRANSACTIONID', self::TRANSACTION_TABLE)) {
            $sQuery = "ALTER TABLE " . self::TRANSACTION_TABLE . " ADD UNIQUE INDEX (`TRANSACTIONID`)";
            self::$_oDb->execute($sQuery);
        }
    }

    /**
     * Migration steps from version 1.1.0 to 1.2.0
     *
     * @throws DatabaseErrorException
     *
     * @since 1.2.0
     */
    private static function _migrateFrom110To120()
    {
        $oDbMetaDataHandler = oxNew(DbMetaDataHandler::class);

        if (!$oDbMetaDataHandler->tableExists(self::PAYMENT_METADATA_TABLE)) {
            self::createPaymentMethodMetaDataTable();
        }

        // adds new enum to orderState field
        $sQuery = "ALTER TABLE " . self::ORDER_TABLE .
            " MODIFY `WDOXIDEE_ORDERSTATE` enum('" . implode("','", Order::getStates()) . "') default '" .
            Order::getStates()[0] . "' NOT NULL";
        self::$_oDb->execute($sQuery);
    }

    /**
     * Migration steps from version 1.2.0 to 1.3.0
     *
     * @throws DatabaseErrorException
     *
     * @since 1.3.0
     */
    private static function _migrateFrom120To130()
    {
        $oDbMetaDataHandler = oxNew(DbMetaDataHandler::class);

        if (!$oDbMetaDataHandler->tableExists(self::VAULT_TABLE)) {
            self::_createVaultTable();
        }
    }
}
