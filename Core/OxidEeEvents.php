<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\DbMetaDataHandler;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Extend\Model\Order;
use Wirecard\Oxid\Model\Transaction;
use Wirecard\Oxid\Model\SepaDirectDebitPaymentMethod;

/**
 * Class handles module behaviour on shop installation events
 *
 *
 * @since 1.0.0
 */
class OxidEeEvents
{
    const OBJECT_PAYMENT_TABLE = "oxobject2payment";
    const ORDER_TABLE = "oxorder";
    const PAYMENT_TABLE = "oxpayments";
    const TRANSACTION_TABLE = "wdoxidee_ordertransactions";

    private static $oDb;

    /**
     * Database helper function
     * Executes the query if the specified column does not exist in the table.
     *
     * @param string $sTableName  database table name
     * @param string $sColumnName database column name
     * @param string $sQuery      SQL query to execute if column does not exist in the table
     *
     * @return boolean true or false if query was executed
     *
     * @since 1.0.0
     */
    private static function _addColumnIfNotExists($sTableName, $sColumnName, $sQuery)
    {
        $aColumns = self::$oDb->getAll("SHOW COLUMNS FROM {$sTableName} LIKE '{$sColumnName}'");

        if (!$aColumns || count($aColumns) === 0) {
            try {
                self::$oDb->Execute($sQuery);
                return true;
            } catch (Exception $oException) {
            }
        }

        return false;
    }

    /**
     * Regenerates database view-tables
     *
     * @since 1.0.0
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
     *
     * @since 1.0.0
     */
    private static function _insertRowIfNotExists($sTableName, $aKeyValue, $sQuery)
    {
        $sWhere = '';

        foreach ($aKeyValue as $sKey => $sValue) {
            $sWhere .= " AND $sKey = '$sValue'";
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
     *
     * @since 1.0.0
     */
    private static function _extendPaymentMethodTable()
    {
        $sQueryAddLabel = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_LABEL` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_LABEL', $sQueryAddLabel);

        $sQueryAddLogo = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_LOGO` varchar(256) default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_LOGO', $sQueryAddLogo);

        $aTransactionActions = Transaction::getActions();
        $sTransactionActions = implode("','", $aTransactionActions);
        $sQueryAddTransAction = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_TRANSACTIONACTION` enum('{$sTransactionActions}') NOT NULL";
        self::_addColumnIfNotExists('oxpayments', 'WDOXIDEE_TRANSACTIONACTION', $sQueryAddTransAction);
        $sQueryAddApiUrl = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_APIURL` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_APIURL', $sQueryAddApiUrl);

        $sQueryAddMaid = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_MAID` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_MAID', $sQueryAddMaid);

        $sQueryAddIsOurs = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_ISOURS` tinyint(1) default 0 NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_ISOURS', $sQueryAddIsOurs);

        $sQueryAddSecret = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_SECRET` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_SECRET', $sQueryAddSecret);

        $sQueryAddThreeDMaid = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_THREE_D_MAID` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_THREE_D_MAID', $sQueryAddThreeDMaid);

        $sQueryThreeDSecret = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_THREE_D_SECRET` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_THREE_D_SECRET', $sQueryThreeDSecret);

        $sQueryAddMaxLimit = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_NON_THREE_D_MAX_LIMIT` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_NON_THREE_D_MAX_LIMIT', $sQueryAddMaxLimit);

        $sQueryAddMinLimit = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_THREE_D_MIN_LIMIT` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_THREE_D_MIN_LIMIT', $sQueryAddMinLimit);

        $sQueryLimitsCurrency = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_LIMITS_CURRENCY` varchar(128) default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_LIMITS_CURRENCY', $sQueryLimitsCurrency);

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

        $sQueryAddInfo = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_ADDITIONAL_INFO` tinyint(1) default 0 NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_ADDITIONAL_INFO', $sQueryAddInfo);

        $sQueryAddDelCanceled = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_DELETE_CANCELED_ORDER` tinyint(1) default 0 NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_DELETE_CANCELED_ORDER', $sQueryAddDelCanceled);

        $sQueryAddDelFailed = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_DELETE_FAILED_ORDER` tinyint(1) default 0 NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_DELETE_FAILED_ORDER', $sQueryAddDelFailed);

        $sQueryAddCountryCode = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_COUNTRYCODE` varchar(5) default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_COUNTRYCODE', $sQueryAddCountryCode);

        $sQueryAddLogoVariant = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_LOGOVARIANT` enum('standard', 'descriptive')";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_LOGOVARIANT', $sQueryAddLogoVariant);

        self::_addSepaDDFields();
    }

    /**
     * Adds SEPA Direct Debit related fields to payment table
     *
     * @since 1.0.1
     */
    private static function _addSepaDDFields()
    {
        $sQueryAddBic = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_BIC` tinyint(1) default 0 NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_BIC', $sQueryAddBic);

        $sQueryAddCreditorId = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_CREDITORID` varchar(35) default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_CREDITORID', $sQueryAddCreditorId);

        $sQueryAddSepaMand = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_SEPAMANDATECUSTOM` text default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_SEPAMANDATECUSTOM', $sQueryAddSepaMand);

        $sQueryAddSepaMand_1 = "ALTER TABLE " . self::PAYMENT_TABLE .
            " ADD COLUMN `WDOXIDEE_SEPAMANDATECUSTOM_1` text default '' NOT NULL";
        self::_addColumnIfNotExists(self::PAYMENT_TABLE, 'WDOXIDEE_SEPAMANDATECUSTOM_1', $sQueryAddSepaMand_1);
    }

    /**
     * Extends OXID's internal order table with the fields required by the module
     *
     * @since 1.0.0
     */
    private static function _extendOrderTable()
    {
        $aOrderStates = Order::getStates();
        $sOrderStates = implode("','", $aOrderStates);
        $sAddOrderState = "ALTER TABLE oxorder ADD COLUMN `WDOXIDEE_ORDERSTATE`
            enum('{$sOrderStates}') default '{$aOrderStates[0]}' NOT NULL";
        self::_addColumnIfNotExists('oxorder', 'WDOXIDEE_ORDERSTATE', $sAddOrderState);

        $sAddCaptureAmount = "ALTER TABLE oxorder ADD COLUMN `WDOXIDEE_CAPTUREAMOUNT` decimal(9,2) NOT NULL";
        self::_addColumnIfNotExists('oxorder', 'WDOXIDEE_CAPTUREAMOUNT', $sAddCaptureAmount);

        $sAddRefundedAmount = "ALTER TABLE oxorder ADD COLUMN `WDOXIDEE_REFUNDEDAMOUNT` decimal(9,2) NOT NULL";
        self::_addColumnIfNotExists('oxorder', 'WDOXIDEE_REFUNDEDAMOUNT', $sAddRefundedAmount);

        $sAddVoidedAmount = "ALTER TABLE oxorder ADD COLUMN `WDOXIDEE_VOIDEDAMOUNT` decimal(9,2) NOT NULL";
        self::_addColumnIfNotExists('oxorder', 'WDOXIDEE_VOIDEDAMOUNT', $sAddVoidedAmount);

        $sAddFinal = "ALTER TABLE oxorder ADD COLUMN `WDOXIDEE_FINAL` tinyint(1) default 0 NOT NULL";
        self::_addColumnIfNotExists('oxorder', 'WDOXIDEE_FINAL', $sAddFinal);

        $sAddProviderTID = "ALTER TABLE oxorder ADD COLUMN `WDOXIDEE_PROVIDERTRANSACTIONID` varchar(36) NOT NULL";
        self::_addColumnIfNotExists(
            'oxorder',
            'WDOXIDEE_PROVIDERTRANSACTIONID',
            $sAddProviderTID
        );

        $sAddTransactionID = "ALTER TABLE oxorder ADD COLUMN `WDOXIDEE_TRANSACTIONID` varchar(36) NOT NULL";
        self::_addColumnIfNotExists('oxorder', 'WDOXIDEE_TRANSACTIONID', $sAddTransactionID);

        $sAddFinalizeOrder = "ALTER TABLE oxorder ADD COLUMN `WDOXIDEE_FINALIZEORDERSTATE` int NOT NULL";
        self::_addColumnIfNotExists('oxorder', 'WDOXIDEE_FINALIZEORDERSTATE', $sAddFinalizeOrder);

        $sAddSepaMandate = "ALTER TABLE oxorder ADD COLUMN `WDOXIDEE_SEPAMANDATE` text";
        self::_addColumnIfNotExists('oxorder', 'WDOXIDEE_SEPAMANDATE', $sAddSepaMandate);
    }

    /**
     * Creates the module's order transaction table
     *
     * @since 1.0.0
     */
    private static function _createOrderTransactionTable()
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

        self::$oDb->Execute($sQuery);
    }

    /**
     * Add Wirecard's payment methods defined in payments.xml
     *
     * @return undefined
     *
     * @since 1.0.0
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
     *
     * @since 1.0.0
     */
    private static function _addPaymentMethod($oPayment)
    {
        $aKeyValue = [
            "OXID" => $oPayment->oxid,
        ];

        $sQuery = "INSERT INTO " . self::PAYMENT_TABLE . "(`OXID`, `OXACTIVE`, `OXTOAMOUNT`, `OXDESC`, `OXDESC_1`,
         `OXSORT`, `WDOXIDEE_LOGO`, `WDOXIDEE_TRANSACTIONACTION`, `WDOXIDEE_APIURL`, `WDOXIDEE_MAID`,
         `WDOXIDEE_SECRET`, `WDOXIDEE_THREE_D_MAID`, `WDOXIDEE_THREE_D_SECRET`, `WDOXIDEE_NON_THREE_D_MAX_LIMIT`,
         `WDOXIDEE_THREE_D_MIN_LIMIT`, `WDOXIDEE_LIMITS_CURRENCY`, `WDOXIDEE_HTTPUSER`, `WDOXIDEE_HTTPPASS`,
         `WDOXIDEE_ISOURS`, `WDOXIDEE_BASKET`, `WDOXIDEE_DESCRIPTOR`, `WDOXIDEE_ADDITIONAL_INFO`,
         `WDOXIDEE_COUNTRYCODE`, `WDOXIDEE_LOGOVARIANT`, `WDOXIDEE_CREDITORID`) VALUES (
             '{$oPayment->oxid}',
             '{$oPayment->oxactive}',
             '{$oPayment->oxtoamount}',
             '{$oPayment->oxdesc}',
             '{$oPayment->oxdesc_1}',
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
        $bIsInserted = self::_insertRowIfNotExists(self::PAYMENT_TABLE, $aKeyValue, $sQuery);
        if ((string) $oPayment->oxid === SepaDirectDebitPaymentMethod::getName(true) && $bIsInserted) {
            self::_insertSepaMandate();
        }

        $sRandomOxidId = substr(str_shuffle(md5(time())), 0, 15);

        // insert payment method configuration (necessary for making the payment visible in the checkout page)
        self::_insertRowIfNotExists(
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
     * Adds initial SEPA mandate text to SEPA Direct Debit payment method
     *
     * @since 1.1.0
     */
    private static function _insertSepaMandate()
    {
        $sSepaMandate = self::_prepareSepaMandate(0);
        $sSepaMandate1 = self::_prepareSepaMandate(1);
        $sPaymentId = SepaDirectDebitPaymentMethod::getName(true);
        $sQuery = "UPDATE oxpayments SET `WDOXIDEE_SEPAMANDATECUSTOM` = '$sSepaMandate', `WDOXIDEE_SEPAMANDATECUSTOM_1`
            = '$sSepaMandate1' WHERE `OXID` LIKE " . "'" . $sPaymentId . "'";
        self::$oDb->execute($sQuery);
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
     * @since 1.0.0
     */
    public static function onActivate()
    {
        self::$oDb = DatabaseProvider::getDb();

        // extend OXID's payment method table
        self::_extendPaymentMethodTable();

        // extend OXID's order table
        self::_extendOrderTable();

        // create the module's own order transaction table
        self::_createOrderTransactionTable();

        // needs to be executed before _addPaymentMethods()
        self::_migrateFrom100To101();

        self::_addPaymentMethods();

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
     *
     * @since 1.0.0
     */
    public static function onDeactivate()
    {
        self::$oDb = DatabaseProvider::getDb();

        self::_disablePaymentMethods();
    }

    /**
     * Deactivate wirecard payment methods
     *
     * @since 1.0.0
     */
    private static function _disablePaymentMethods()
    {
        $sQuery = "UPDATE oxpayments SET `OXACTIVE` = 0 WHERE `OXID` LIKE 'wd%'";

        self::$oDb->execute($sQuery);
    }

    /**
     * Migration steps from version 1.0.0 to 1.0.1
     *
     * @since 1.0.1
     */
    private static function _migrateFrom100To101()
    {
        $oDbMetaDataHandler = oxNew(DbMetaDataHandler::class);

        if (!$oDbMetaDataHandler->fieldExists('TRANSACTIONNUMBER', self::TRANSACTION_TABLE)) {
            // adds a sortable transaction number
            $sQuery = "ALTER TABLE " . self::TRANSACTION_TABLE .
                " DROP PRIMARY KEY, ADD COLUMN `TRANSACTIONNUMBER` int AUTO_INCREMENT NOT NULL PRIMARY KEY";
            self::$oDb->execute($sQuery);

            // adds new enum to transactionActions field
            $sTransactionActions = implode("','", Transaction::getActions());

            $sQuery = "ALTER TABLE " . self::TRANSACTION_TABLE .
                " MODIFY `ACTION` enum('{$sTransactionActions}')";
            self::$oDb->Execute($sQuery);

            $sQuery = "ALTER TABLE " . self::PAYMENT_TABLE .
                " MODIFY COLUMN `WDOXIDEE_TRANSACTIONACTION` enum('{$sTransactionActions}')";
            self::$oDb->Execute($sQuery);
        }
    }
}
