<?php

/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use \OxidEsales\Eshop\Core\DatabaseProvider;
use \OxidEsales\Eshop\Core\Registry;

/**
 * Class handles module behaviour on shop installation events
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.Coverage)
 */
class OxidEE_Events
{
    const OBJECTPAYMENT_TABLE = "oxobject2payment";
    const ORDER_TABLE = "oxorder";
    const PAYMENT_TABLE = "oxpayments";
    const TRANSACTION_TABLE = "wdoxidee_ordertransactions";
    private static $oDb;

    /**
     * Database helper function
     * Executes the query if the specified column does not exist in the table.
     *
     * @param string $sTableName database table name
     * @param string $sColumnName database column name
     * @param string $sQuery SQL query to execute if column does not exist in the table
     *
     * @return boolean true or false if query was executed
     */
    private static function _addColumnIfNotExists($sTableName, $sColumnName, $sQuery)
    {

        $aColumns = self::$oDb->getAll("SHOW COLUMNS FROM {$sTableName} LIKE '{$sColumnName}'");

        if (!$aColumns || count($aColumns) === 0) {
            try {
                self::$oDb->Execute($sQuery);
                return true;
            } catch (Exception $e) {
            }
        }

        return false;
    }

    /**
     * Regenerates database view-tables
     */
    private static function _regenerateViews()
    {
        $oShop = oxNew('oxShop');
        $oShop->generateViews();
    }

    /**
     * Database helper function
     * Executes the query if no row with the specified criteria exists in the table.
     *
     * @param string $sTableName database table name
     * @param array $aKeyValue key-value array to build where query string
     * @param string $sQuery SQL query to execute if no row with the search criteria exists in the table
     *
     * @return boolean true or false if query was executed
     */
    private static function _insertRowIfNotExists($sTableName, $aKeyValue, $sQuery)
    {

        $sWhere = '';

        foreach ($aKeyValue as $key => $value) {
            $sWhere .= " AND $key = '$value'";
        }

        $sCheckQuery = "SELECT * FROM {$sTableName} WHERE 1" . $sWhere;
        $sExisting = self::$oDb->getOne($sCheckQuery);

        if (!$sExisting) {
            self::$oDb->Execute($sQuery);
            return true;
        }

        return false;
    }

    /**
     * Extends OXID's internal payment methods table with the fields required by the module
     */
    private static function _extendPaymentMethodTable()
    {
        $sQueryAddLabel = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_LABEL` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_LABEL', $sQueryAddLabel);

        $sQueryAddLogo = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_LOGO` varchar(256) default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_LOGO', $sQueryAddLogo);

        $sQueryAddTransType = "ALTER TABLE " . self::PAYMENT_TABLE . " ADD COLUMN `WDOXIDEE_TRANSACTIONTYPE`
            enum('authorize-capture','purchase') default 'authorize-capture' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_TRANSACTIONTYPE', $sQueryAddTransType);

        $sQueryAddApiUrl = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_APIURL` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_APIURL', $sQueryAddApiUrl);

        $sQueryAddMaid = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_MAID` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_MAID', $sQueryAddMaid);

        $sQueryAddIsWirecard = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_ISWIRECARD` tinyint(1) default 0 NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_ISWIRECARD', $sQueryAddIsWirecard);

        $sQueryAddSecret = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_SECRET` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_SECRET', $sQueryAddSecret);

        $sQueryAddHttpUser = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_HTTPUSER` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_HTTPUSER', $sQueryAddHttpUser);

        $sQueryAddHttpPass = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_HTTPPASS` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_HTTPPASS', $sQueryAddHttpPass);

        $sQueryAddBasket = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_BASKET` tinyint(1) default 0 NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_BASKET', $sQueryAddBasket);

        $sQueryAddDescriptor = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_DESCRIPTOR` tinyint(1) default 0 NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_DESCRIPTOR', $sQueryAddDescriptor);

        $sQueryAddInfo = "ALTER TABLE " . self::PAYMENT_TABLE . " ADD COLUMN `WDOXIDEE_ADDITIONAL_INFO`
         tinyint(1) default 0 NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_ADDITIONAL_INFO', $sQueryAddInfo);
    }

    /**
     * Extends OXID's internal order table with the fields required by the module
     */
    private static function _extendOrderTable()
    {
        $sQueryState = "ALTER TABLE " . self::ORDER_TABLE .
            " ADD COLUMN `WDOXIDEE_PAYMENTSTATE` enum('pending','completed',
        'failed','cancelled')";
        self::_addColumnIfNotExists(self::ORDER_TABLE, 'WDOXIDEE_PAYMENTSTATE', $sQueryState);
    }

    /**
     * Creates the module's order transaction table
     */
    private static function _createOrderTransactionTable()
    {
        $sQuery = "CREATE TABLE IF NOT EXISTS " . self::TRANSACTION_TABLE . "(
            `OXID` char(32) NOT NULL,
            `WDOXIDEE_ORDERID` varchar(32) NOT NULL,
            `WDOXIDEE_TRANSACTIONID` varchar(32) NOT NULL,
            `WDOXIDEE_PARENTTRANSACTIONID` varchar(32),
            `WDOXIDEE_REQUESTID` varchar(32) NOT NULL,
            `WDOXIDEE_ACTION` enum('purchase','authorization') NOT NULL DEFAULT 'purchase',
            `WDOXIDEE_STATE` enum('success','error','pending'),
            `WDOXIDEE_PAYMENTMETHOD` varchar(32) NOT NULL,
            `WDOXIDEE_AMOUNT` double NOT NULL,
            `WDOXIDEE_CURRENCY` varchar(32) NOT NULL,
            `WDOXIDEE_RESPONSE` mediumtext NOT NULL,
            `WDOXIDEE_RESPONSEXML` mediumtext NOT NULL,
            `WDOXIDEE_DATE` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`OXID`)
        ) Engine=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

        self::$oDb->Execute($sQuery);
    }

    /**
     * Delete the module's order transaction table
     * ONLY FOR DEVELOPMENT PURPOSES, NOT TO BE USED IN PRODUCTION!
     */
    private static function _deleteOrderTransactionTable()
    {
        $sQuery = "DROP TABLE IF EXISTS " . self::TRANSACTION_TABLE;

        self::$oDb->Execute($sQuery);
    }

    /**
     * Add Wirecard's payment methods defined in payments.xml
     *
     * @return undefined
     */
    private static function _addPaymentMethods()
    {
        $oLogger = Registry::getLogger();
        $oConfig = Registry::getConfig();
        $sShopBaseURL = $oConfig->getShopUrl();
        $oXmldata = simplexml_load_file($sShopBaseURL . "modules/wirecard/paymentgateway/default_payment_config.xml");

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
     */
    private static function _addPaymentMethod($oPayment)
    {
        $aKeyValue = array(
            "OXID" => $oPayment->oxid
        );

        $sQuery = "INSERT INTO " . self::PAYMENT_TABLE . "(`OXID`, `OXACTIVE`, `OXTOAMOUNT`, `OXDESC`, `OXDESC_1`,
         `WDOXIDEE_LOGO`, `WDOXIDEE_TRANSACTIONTYPE`, `WDOXIDEE_APIURL`, `WDOXIDEE_MAID`, `WDOXIDEE_SECRET`,
         `WDOXIDEE_HTTPUSER`, `WDOXIDEE_HTTPPASS`, `WDOXIDEE_ISWIRECARD`, `WDOXIDEE_BASKET`,
         `WDOXIDEE_DESCRIPTOR`, `WDOXIDEE_ADDITIONAL_INFO`) VALUES (
             '{$oPayment->oxid}',
             '{$oPayment->oxactive}',
             '{$oPayment->oxtoamount}',
             '{$oPayment->oxdesc}',
             '{$oPayment->oxdesc_1}',
             '{$oPayment->wdoxidee_logo}',
             '{$oPayment->wdoxidee_transactiontype}',
             '{$oPayment->wdoxidee_apiurl}',
             '{$oPayment->wdoxidee_maid}',
             '{$oPayment->wdoxidee_secret}',
             '{$oPayment->wdoxidee_httpuser}',
             '{$oPayment->wdoxidee_httppass}',
             '1',
             '{$oPayment->wdoxidee_basket}',
             '{$oPayment->wdoxidee_descriptor}',
             '{$oPayment->wdoxidee_additional_info}'
        );";

        // insert payment method
        self::_insertRowIfNotExists(self::PAYMENT_TABLE, $aKeyValue, $sQuery);

        $sRandomOxidId = substr(str_shuffle(md5(time())), 0, 15);

        // insert payment method configuration (necessary for making the payment visible in the checkout page)
        self::_insertRowIfNotExists(
            self::OBJECTPAYMENT_TABLE,
            array('OXPAYMENTID' => $oPayment->oxid),
            "INSERT INTO " . self::OBJECTPAYMENT_TABLE . " (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) VALUES (
                '{$sRandomOxidId}',
                '{$oPayment->oxid}',
                'oxidstandard',
                'oxdelset'
            );"
        );
    }

    /**
     * Handle OXID's onActivate event
     */
    public static function onActivate()
    {
        self::$oDb = DatabaseProvider::getDb();

        // extend OXID's payment method table
        self::_extendPaymentMethodTable();

        // extend OXID's order table
        self::_extendOrderTable();

        self::_addPaymentMethods();

        // create the module's own order transaction table
        self::_createOrderTransactionTable();

        // view tables must be regenerated after modifying database table structure
        self::_regenerateViews();

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
     * Handle OXID's onDeactivate event
     */
    public static function onDeactivate()
    {
        self::$oDb = DatabaseProvider::getDb();
        // read the OXID_ENVIRONMENT variable from the .env and docker-compose files
        $environmentVar = getenv('OXID_ENVIRONMENT');

        // if development, delete the database tables on module de-activation
        if ($environmentVar === 'development') {
            self::_deleteOrderTransactionTable();
        }

        self::_disablePaymentTypes();
    }

    private static function _disablePaymentTypes()
    {
        $sQuery = "UPDATE oxpayments SET `OXACTIVE` = 0 WHERE `OXID` LIKE 'wd%'";

        $iNumRowsAffected = self::$oDb->execute($sQuery);

        Registry::getLogger()->info("$iNumRowsAffected payment methods disabled");
    }
}
