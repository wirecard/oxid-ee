<?php

/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use \oxDb;

/**
 * Class handles module behaviour on shop installation events
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.Coverage)
 */
class OxidEE_Events
{
    const PAYMENT_TABLE_NAME = "oxpayments";
    /**
     * Database helper function
     * Executes the query if the specified column does not exist in the table.
     *
     * @param string $sTableName  database table name
     * @param string $sColumnName database column name
     * @param string $sQuery      SQL query to execute if column does not exist in the table
     *
     * @return boolean true or false if query was executed
     */
    private static function _addColumnIfNotExists($sTableName, $sColumnName, $sQuery)
    {
        $oDb = oxDb::getDb();

        $aColumns = $oDb->getAll("SHOW COLUMNS FROM {$sTableName} LIKE '{$sColumnName}'");

        if (!$aColumns || count($aColumns) === 0) {
            try {
                $oDb->Execute($sQuery);
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
     * @param array  $aKeyValue  key-value array to build where query string
     * @param string $sQuery     SQL query to execute if no row with the search criteria exists in the table
     *
     * @return boolean true or false if query was executed
     */
    private static function _insertRowIfNotExists($sTableName, $aKeyValue, $sQuery)
    {
        $oDb = oxDb::getDb();

        $sWhere = '';

        foreach ($aKeyValue as $key => $value) {
            $sWhere .= " AND $key = '$value'";
        }

        $sCheckQuery = "SELECT * FROM {$sTableName} WHERE 1" . $sWhere;
        $sExisting = $oDb->getOne($sCheckQuery);

        if (!$sExisting) {
            $oDb->Execute($sQuery);
            return true;
        }

        return false;
    }

    /**
     * Extends OXID's internal payment methods table with the fields required by the module
     */
    private static function _extendPaymentMethodTable()
    {
        $sQueryAddLabel = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_LABEL` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_LABEL', $sQueryAddLabel);

        $sQueryAddLogo = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_LOGO` varchar(256) default '' NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_LOGO', $sQueryAddLogo);

        $sQueryAddTransType = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_TRANSACTIONTYPE`
            enum('authorize-capture','purchase') default 'authorize-capture' NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_TRANSACTIONTYPE', $sQueryAddTransType);

        $sQueryAddApiUrl = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_APIURL` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_APIURL', $sQueryAddApiUrl);

        $sQueryAddMaid = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_MAID` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_MAID', $sQueryAddMaid);

        $sQueryAddIsWirecard = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_ISWIRECARD` tinyint(1) default 0 NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_ISWIRECARD', $sQueryAddIsWirecard);

        $sQueryAddSecret = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_SECRET` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_SECRET', $sQueryAddSecret);

        $sQueryAddHttpUser = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_HTTPUSER` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_HTTPUSER', $sQueryAddHttpUser);

        $sQueryAddHttpPass = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_HTTPPASS` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_HTTPPASS', $sQueryAddHttpPass);

        $sQueryAddBasket = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_BASKET` tinyint(1) default 0 NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_BASKET', $sQueryAddBasket);

        $sQueryAddDescriptor = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_DESCRIPTOR` tinyint(1) default 0 NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_DESCRIPTOR', $sQueryAddDescriptor);

        $sQueryAddAdditionalInfo = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_ADDITIONAL_INFO`
         tinyint(1) default 0 NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_ADDITIONAL_INFO', $sQueryAddAdditionalInfo);
    }

    /**
     * Extends OXID's internal order table with the fields required by the module
     */
    private static function _extendOrderTable()
    {
        $sQueryAddPaymentState = "ALTER TABLE oxorder ADD COLUMN `WDOXIDEE_PAYMENTSTATE` enum('pending','completed',
        'failed','cancelled')";
        self::_addColumnIfNotExists('oxorder', 'WDOXIDEE_PAYMENTSTATE', $sQueryAddPaymentState);

        $sQueryAddCaptureAmount = "ALTER TABLE oxorder ADD COLUMN `WDOXIDEE_CAPTUREAMOUNT` decimal(9,2) NOT NULL";
        self::_addColumnIfNotExists('oxorder', 'WDOXIDEE_CAPTUREAMOUNT', $sQueryAddCaptureAmount);

        $sQueryAddRefundedAmount = "ALTER TABLE oxorder ADD COLUMN `WDOXIDEE_REFUNDEDAMOUNT` decimal(9,2) NOT NULL";
        self::_addColumnIfNotExists('oxorder', 'WDOXIDEE_REFUNDEDAMOUNT', $sQueryAddRefundedAmount);

        $sQueryAddVoidedAmount = "ALTER TABLE oxorder ADD COLUMN `WDOXIDEE_VOIDEDAMOUNT` decimal(9,2) NOT NULL";
        self::_addColumnIfNotExists('oxorder', 'WDOXIDEE_VOIDEDAMOUNT', $sQueryAddVoidedAmount);
    }

    /**
     * Creates the module's order transaction table
     */
    private static function _createOrderTransactionTable()
    {
        $sQuery = "CREATE TABLE IF NOT EXISTS `wdoxidee_ordertransactions`(
            `OXID` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `WDOXIDEE_ORDERNUMBER` varchar(32) NOT NULL,
            `WDOXIDEE_ORDERID` varchar(32) NOT NULL,
            `WDOXIDEE_TRANSACTIONID` varchar(32) NOT NULL,
            `WDOXIDEE_PARENTTRANSACTIONID` varchar(32),
            `WDOXIDEE_ACTION` enum('purchase','authorization') NOT NULL DEFAULT 'purchase',
            `WDOXIDEE_STATUS` enum('success','error','pending'),
            `WDOXIDEE_TRANSACTIONTYPE` varchar(32),
            `WDOXIDEE_STATE` varchar(32) NOT NULL,
            `WDOXIDEE_AMOUNT` decimal(9,2) NOT NULL,
            `WDOXIDEE_CURRENCY` varchar(32) NOT NULL,
            `WDOXIDEE_IPADDRESS` varchar(32) NOT NULL,
            `WDOXIDEE_REQUESTID` varchar(32) NOT NULL,
            `WDOXIDEE_DESCRIPTOR` varchar(32) NOT NULL,
            `WDOXIDEE_TRANSACTIONDATE` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`OXID`)
        ) Engine=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

        $oDb = oxDb::getDb();
        $oDb->Execute($sQuery);
    }

    /**
     * Delete the module's order transaction table
     * ONLY FOR DEVELOPMENT PURPOSES, NOT TO BE USED IN PRODUCTION!
     */
    private static function _deleteOrderTransactionTable()
    {
        $sQuery = "DROP TABLE IF EXISTS `wdoxidee_ordertransactions`";

        $oDb = oxDb::getDb();
        $oDb->Execute($sQuery);
    }

    /**
     * Handle OXID's onActivate event
     */
    public static function onActivate()
    {
        // extend OXID's payment method table
        self::_extendPaymentMethodTable();

        // extend OXID's payment method table
        self::_extendOrderTable();

        // create the module's own order transaction table
        self::_createOrderTransactionTable();

        $array = array(
            "OXID" => "wdpaypal"
        );
        $sQuery = "INSERT INTO " . 'oxpayments' . "(`OXID`, `OXACTIVE`, `OXTOAMOUNT`, `OXDESC`, `OXDESC_1`,
        `WDOXIDEE_LOGO`, `WDOXIDEE_TRANSACTIONTYPE`, `WDOXIDEE_APIURL`, `WDOXIDEE_MAID`,
        `WDOXIDEE_SECRET`, `WDOXIDEE_HTTPUSER`, `WDOXIDEE_HTTPPASS`, `WDOXIDEE_ISWIRECARD`,
         `WDOXIDEE_BASKET`, `WDOXIDEE_DESCRIPTOR`, `WDOXIDEE_ADDITIONAL_INFO`)
         VALUES ('wdpaypal', 0, 1000000, 'Wirecard PayPal', 'Wirecard PayPal', 'paypal.png', 'purchase',
         'https://api-test.wirecard.com', '2a0e9351-24ed-4110-9a1b-fd0fee6bec26',
         'dbc5a498-9a66-43b9-bf1d-a618dd399684', '70000-APITEST-AP', 'qD2wzQ_hrc!8', 1, 1, 1, 1);";
        self::_insertRowIfNotExists('oxpayments', $array, $sQuery);

        $random = substr(str_shuffle(md5(time())), 0, 15);
        self::_insertRowIfNotExists(
            'oxobject2payment',
            array('OXPAYMENTID' => 'wdpaypal'),
            "INSERT INTO oxobject2payment (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) VALUES
         ('{$random}', 'wdpaypal', 'oxidstandard', 'oxdelset');"
        );

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
        // read the OXID_ENVIRONMENT variable from the .env and docker-compose files
        $environmentVar = getenv('OXID_ENVIRONMENT');

        // if development, delete the database tables on module de-activation
        if ($environmentVar === 'development') {
            self::_deleteOrderTransactionTable();
        }
    }
}
