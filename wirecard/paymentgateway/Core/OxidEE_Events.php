<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

/**
 * Class handles module behaviour on shop installation events
 */
class OxidEE_Events
{
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
     * Database helper function
     * Executes the query if a row with the specified criteria exists in the table.
     *
     * @param string $sTableName database table name
     * @param array $aKeyValue key-value array to build where query string
     *
     * @return boolean true or false if query was executed
     */
    private static function _deleteRowIfExists($sTableName, $aKeyValue)
    {
        $oDb = oxDb::getDb();

        $sWhere = '';

        foreach ($aKeyValue as $key => $value) {
            $sWhere .= " AND $key = '$value'";
        }

        if ($oDb->getOne("SELECT * FROM {$sTableName} WHERE 1" . $sWhere)) {
            $oDb->Execute("DELETE FROM {$sTableName} WHERE 1" . $sWhere);
            return true;
        }

        return false;
    }

    /**
     * Database helper function
     * Executes the query if the column type does not match the exepcted criteria.
     *
     * @param string $sTableName database table name
     * @param string $sColumnName database column name
     * @param string $sExpectedType expected column type
     * @param string $sQuery SQL query to execute if no column with the expected structure exists
     *
     * @return boolean true or false if query was executed
     */
    private static function _changeColumnTypeIfWrong($sTableName, $sColumnName, $sExpectedType, $sQuery)
    {
        $oDb = oxDb::getDb();

        if (!$oDb->getOne("SHOW COLUMNS FROM {$sTableName} WHERE FIELD = '{$sColumnName}' AND TYPE = '{$sExpectedType}'")) {
            $oDb->Execute($sQuery);
            return true;
        }

        return false;
    }

    /**
     * Regenerates database view-tables
     *
     * @return void
     */
    private static function _regenerateViews()
    {
        $oShop = oxNew('oxShop');
        $oShop->generateViews();
    }

    /**
     * Extends OXID's internal payment methods table with the fields required by the module
     *
     * @return void
     */
    private static function _extendPaymentMethodTable()
    {
        $sQueryAlterPaymentsTableLabel = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_LABEL` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_LABEL', $sQueryAlterPaymentsTableLabel);

        $sQueryAlterPaymentsTableLogo = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_LOGO` varchar(256) default '' NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_LOGO', $sQueryAlterPaymentsTableLogo);

        $sQueryAlterPaymentsTableTransactionType = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_TRANSACTIONTYPE` enum('authorize-capture','purchase') default 'authorize-capture' NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_TRANSACTIONTYPE', $sQueryAlterPaymentsTableTransactionType);

        $sQueryAlterPaymentsTableApiUrl = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_APIURL` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_APIURL', $sQueryAlterPaymentsTableApiUrl);

        $sQueryAlterPaymentsTableMaid = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_MAID` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_MAID', $sQueryAlterPaymentsTableMaid);

        $sQueryAlterPaymentsTableSecret = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_SECRET` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_SECRET', $sQueryAlterPaymentsTableSecret);

        $sQueryAlterPaymentsTableHttpUser = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_HTTPUSER` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_HTTPUSER', $sQueryAlterPaymentsTableHttpUser);

        $sQueryAlterPaymentsTableHttpPass = "ALTER TABLE oxpayments ADD COLUMN `WDOXIDEE_HTTPPASS` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_HTTPPASS', $sQueryAlterPaymentsTableHttpPass);
    }

    /**
     * Creates the module's order table
     *
     * @return void
     */
    private static function _createOrderTable()
    {
        $sQuery = "CREATE TABLE IF NOT EXISTS `wdoxidee_orders` (
                `wdoxidee_orderid` char(32) character set latin1 collate latin1_general_ci NOT NULL,
                `wdoxidee_paymentstate` enum('pending','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
                `wdoxidee_totalordersum` decimal(9,2) NOT NULL,
                `wdoxidee_capturedamount` decimal(9,2) NOT NULL,
                `wdoxidee_refundedamount` decimal(9,2) NOT NULL,
                `wdoxidee_voidedamount` decimal(9,2) NOT NULL,
                `wdoxidee_currency` varchar(32) NOT NULL,
                `wdoxidee_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
                PRIMARY KEY (`wdoxidee_orderid`),
                KEY `wdoxidee_paymentstate` (`wdoxidee_paymentstate`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

            $oDb = oxDb::getDb();
            $oDb->Execute($sQuery);
    }

    /**
     * Creates the module's order transaction table
     *
     * @return void
     */
    private static function _createOrderTransactionTable()
    {
        $sQuery = "CREATE TABLE IF NOT EXISTS `wdoxidee_ordertransactions`(
            `wdoxidee_transactionid` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `wdoxidee_orderid` char(32) character set latin1 collate latin1_general_ci NOT NULL,
            `wdoxidee_requestid` varchar(256) NOT NULL,
            `wdoxidee_transactiontype` varchar(128) NOT NULL,
            `wdoxidee_amount` decimal(9,2) NOT NULL,
            `wdoxidee_refundedamount` decimal(9,2) NOT NULL,
            `wdoxidee_currency` varchar(32) NOT NULL,
            `wdoxidee_transactiondate` datetime NOT NULL,
            `wdoxidee_transactionstatus` enum('pending','success','error') NOT NULL DEFAULT 'pending',
            `wdoxidee_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `wdoxidee_comment` mediumtext,
            PRIMARY KEY (`wdoxidee_transactionid`),
            KEY `wdoxidee_orderid` (`wdoxidee_orderid`),
            KEY `wdoxidee_transactiondate` (`wdoxidee_transactiondate`)
        ) Engine=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

        $oDb = oxDb::getDb();
        $oDb->Execute($sQuery);
    }

    /**
     * Delete the module's order transaction table
     * ONLY FOR DEVELOPMENT PURPOSES, NOT TO BE USED IN PRODUCTION!
     *
     * @return void
     */
    private static function _deleteOrderTable()
    {
        $sQuery = "DROP TABLE IF EXISTS `wdoxidee_orders`";

        $oDb = oxDb::getDb();
        $oDb->Execute($sQuery);
    }

    /**
     * Delete the module's order transaction table
     * ONLY FOR DEVELOPMENT PURPOSES, NOT TO BE USED IN PRODUCTION!
     *
     * @return void
     */
    private static function _deleteOrderTransactionTable()
    {
        $sQuery = "DROP TABLE IF EXISTS `wdoxidee_ordertransactions`";

        $oDb = oxDb::getDb();
        $oDb->Execute($sQuery);
    }

    /**
     * Handle OXID's onActivate event
     *
     * @return void
     */
    public static function onActivate()
    {
        // extend OXID's payment method table
        self::_extendPaymentMethodTable();

        // create the module's own order table
        self::_createOrderTable();

        // create the module's own order transaction table
        self::_createOrderTransactionTable();

        // view tables must be regenerated after modifying database table structure
        self::_regenerateViews();
    }

    /**
     * Handle OXID's onDeactivate event
     *
     * @return void
     */
    public static function onDeactivate()
    {
        // read the OXID_ENVIRONMENT variable from the .env and docker-compose files
        $environmentVar = getenv('OXID_ENVIRONMENT');

        // if development, delete the database tables on module de-activation
        if ($environmentVar === 'development') {
            self::_deleteOrderTransactionTable();
            self::_deleteOrderTable();
        }
    }
}
