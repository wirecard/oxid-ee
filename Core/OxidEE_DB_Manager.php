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
 * Class handles DB manipulation
 */
class OxidEE_DB_Manager
{
    const CONFIG_TABLE_NAME = "oxconfig";
    const ORDER_TABLE_NAME = "wdoxidee_orders";
    const PAYMENT_TABLE_NAME = "oxpayments";
    const TRANSACTION_TABLE_NAME = "wdoxidee_ordertransactions";

    /**
     * Adds OXID's config in the config table
     *
     * @param string $configId
     * @param string $varName
     * @param string $varType
     * @param string $varValue
     *
     * @throws DatabaseErrorException
     *
     * @return integer Number of rows affected by the SQL statement
     */
    public static function addModuleConfig($configId, $varName, $varType, $varValue)
    {
        $oDb = oxDb::getDb();
        return $oDb->Execute(
            "INSERT INTO " . self::CONFIG_TABLE_NAME . "
            (`OXID`, `OXMODULE`, `OXVARNAME`, `OXVARTYPE`, `OXVARVALUE`) VALUES
            (?, ?, ?, ?, ?)",
            array($configId, 'module:wdoxidee', $varName, $varType, $varValue)
        );
    }

    /**
     * Database helper function
     * Returns config with the requested ID
     *
     * @param string $sconfigId config ID
     *
     * @return object config object
     */
    public static function getModuleConfigById($sconfigId)
    {
        $oDb = oxDb::getDb();
        $config = $oDb->getRow(
            "SELECT * FROM " . self::CONFIG_TABLE_NAME . " WHERE OXID = ?",
            array($sconfigId)
        );
        return $config;
    }

    /**
     * Database helper function
     *
     * @param string $sConfigId config ID
     * @param string $aKeyValue values array
     *
     * @throws DatabaseErrorException
     *
     * @return integer Number of rows affected by the SQL statement
     */
    public static function updateModuleConfig($sConfigId, $aKeyValue)
    {
        $oDb = oxDb::getDb();
        $sQuery = "UPDATE " . self::CONFIG_TABLE_NAME . " SET ";
        $array = array();

        foreach ($aKeyValue as $key => $value) {
            array_push($array, $value);
            $sQuery .= $key . " = ? , ";
        }

        $sQuery = trim($sQuery, ' '); // first trim last space
        $sQuery = trim($sQuery, ','); // then trim trailing and prefixing commas

        $sQuery .= " WHERE OXID = ?";
        array_push($array, $sConfigId);
        return $oDb->Execute(
            $sQuery,
            $array
        );
    }

    /**
     * Database helper function
     * Delete config with the requested ID
     *
     * @param string $sConfigId config ID
     *
     * @throws DatabaseErrorException
     *
     * @return integer Number of rows affected by the SQL statement
     */
    public static function deleteModuleConfigById($sConfigId)
    {
        $oDb = oxDb::getDb();
        return $oDb->Execute(
            "DELETE FROM " . self::CONFIG_TABLE_NAME . " WHERE OXID = ?",
            array($sConfigId)
        );
    }

    /**
     * Database helper function
     * Returns order with the requested ID
     *
     * @param string $sOrderId order ID
     *
     * @return object order object
     */
    public static function getOrderById($sOrderId)
    {
        $oDb = oxDb::getDb();
        $order = $oDb->getRow(
            "SELECT * FROM " . self::ORDER_TABLE_NAME . " WHERE wdoxidee_orderid = ?",
            array($sOrderId)
        );
        return $order;
    }

    /**
     * Database helper function
     *
     * @param string $sOrderId order ID
     * @param string $aKeyValue values array
     *
     * @throws DatabaseErrorException
     *
     * @return integer Number of rows affected by the SQL statement
     */
    public static function updateOrder($sOrderId, $aKeyValue)
    {
        $oDb = oxDb::getDb();
        $sQuery = "UPDATE " . self::ORDER_TABLE_NAME . " SET ";
        $array = array();

        foreach ($aKeyValue as $key => $value) {
            array_push($array, $value);
            $sQuery .= $key . " = ? , ";
        }

        $sQuery = trim($sQuery, ' ');
        $sQuery = trim($sQuery, ',');
        $sQuery .= " WHERE wdoxidee_orderid = ?";
        array_push($array, $sOrderId);
        return $oDb->Execute(
            $sQuery,
            $array
        );
    }

    /**
     * Database helper function
     * Returns transaction with the requested ID
     *
     * @param string $sTransactionId order ID
     *
     * @return object transaction object
     */
    public static function getTransactionById($sTransactionId)
    {
        $oDb = oxDb::getDb();
        $transaction = $oDb->getRow(
            "SELECT * FROM " . self::TRANSACTION_TABLE_NAME . " WHERE wdoxidee_transactionid = ?",
            array($sTransactionId)
        );
        return $transaction;
    }

    /**
     * Database helper function
     * Delete transaction with the requested ID
     *
     * @param string $sTransactionId Transaction ID
     *
     * @throws DatabaseErrorException
     *
     * @return integer Number of rows affected by the SQL statement
     */
    public static function deleteTransactionById($sTransactionId)
    {
        $oDb = oxDb::getDb();
        return $oDb->Execute(
            "DELETE FROM " . self::TRANSACTION_TABLE_NAME . " WHERE wdoxidee_transactionid = ?",
            array($sTransactionId)
        );
    }

    /**
     * Database helper function
     * Returns payment with the requested ID
     *
     * @param string $sPaymentId payment ID
     *
     * @return object payment object
     */
    public static function getPaymentMethodById($sPaymentId)
    {
        $oDb = oxDb::getDb();
        $payment = $oDb->getRow(
            "SELECT * FROM " . self::PAYMENT_TABLE_NAME . " WHERE OXID = ?",
            array($sPaymentId)
        );
        return $payment;
    }

    /**
     * Database helper function
     * Delete payment with the requested ID
     *
     * @param string $sPaymentId Payment ID
     *
     * @throws DatabaseErrorException
     *
     * @return integer Number of rows affected by the SQL statement
     */
    public static function deletePaymentMethodById($sPaymentId)
    {
        $oDb = oxDb::getDb();
        return $oDb->Execute(
            "DELETE FROM " . self::PAYMENT_TABLE_NAME . " WHERE OXID = ?",
            array($sPaymentId)
        );
    }

    /**
     * Database helper function
     *
     * @param string $paymentId payment Id
     * @param string $aKeyValue values array
     *
     * @throws DatabaseErrorException
     *
     * @return integer Number of rows affected by the SQL statement
     */
    public static function updatePaymentMethod($paymentId, $aKeyValue)
    {
        $oDb = oxDb::getDb();
        $sQuery = "UPDATE " . self::PAYMENT_TABLE_NAME . " SET ";
        $array = array();

        foreach ($aKeyValue as $key => $value) {
            array_push($array, $value);
            $sQuery .= $key . " = ? , ";
        }

        $sQuery = trim($sQuery, ' ');
        $sQuery = trim($sQuery, ',');
        $sQuery .= " WHERE OXID = ?";
        array_push($array, $paymentId);
        return $oDb->Execute(
            $sQuery,
            $array
        );
    }

    /**
     * Adds payment in the oxpayments table
     *
     * @param string $configId
     * @param string $description
     * @param string $label
     * @param string $logo
     * @param string $transactionType
     * @param string $apiurl
     * @param string $maid
     * @param string $secret
     * @param string $httpuser
     * @param string $httppass
     *
     * @throws DatabaseErrorException
     *
     * @return integer Number of rows affected by the SQL statement
     */
    public static function addPaymentMethod(
        $configId,
        $description,
        $logo,
        $transactionType,
        $apiurl,
        $maid,
        $secret,
        $httpuser,
        $httppass
    ) {
        $oDb = oxDb::getDb();
        return $oDb->Execute(
            "INSERT INTO " . self::PAYMENT_TABLE_NAME . " (`OXID`, `OXACTIVE`,
            `OXDESC`, `WDOXIDEE_LOGO`, `WDOXIDEE_TRANSACTIONTYPE`,
            `WDOXIDEE_APIURL`, `WDOXIDEE_MAID`, `WDOXIDEE_SECRET`, `WDOXIDEE_HTTPUSER`,
            `WDOXIDEE_HTTPPASS`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            array(
                $configId, '0', $description, $logo, $transactionType, $apiurl,
                $maid, $secret, $httpuser, $httppass
            )
        );
    }
}
