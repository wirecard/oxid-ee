<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

/**
 * Class handles DB manipulation
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.Coverage)
 */
class OxidEE_DB_Manager
{
    const CONFIG_TABLE_NAME = "oxconfig";
    const ORDER_TABLE_NAME = "wdoxidee_orders";
    const PAYMENT_TABLE_NAME = "oxpayments";
    const TRANSACTION_TABLE_NAME = "wdoxidee_ordertransactions";

    // OXID DB object
    private $oDb;

    /**
     * DB Manager constructor
     */
    public function __construct()
    {
        $this->oDb = oxDb::getDb();
    }

    /**
     * Adds OXID's config in the config table
     *ln
     * @param string $configId
     * @param string $varName
     * @param string $varType
     * @param string $varValue
     *
     * @throws DatabaseErrorException
     *
     * @return integer Number of rows affected by the SQL statement
     */
    public function addModuleConfig($configId, $varName, $varType, $varValue)
    {
        return $this->oDb->Execute(
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
     * @param string $sConfigId config ID
     *
     * @return object config object
     */
    public function getModuleConfigById($sConfigId)
    {
        $config = $this->oDb->getRow(
            "SELECT * FROM " . self::CONFIG_TABLE_NAME . " WHERE OXID = ?",
            array($sConfigId)
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
    public function updateModuleConfig($sConfigId, $aKeyValue)
    {
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
        return $this->oDb->Execute(
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
    public function deleteModuleConfigById($sConfigId)
    {
        return $this->oDb->Execute(
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
    public function getOrderById($sOrderId)
    {
        $order = $this->oDb->getRow(
            "SELECT * FROM " . self::ORDER_TABLE_NAME . " WHERE wdoxidee_orderid = ?",
            array($sOrderId)
        );
        return $order;
    }

    /**
     * Database helper function
     *
     * @param string $sOrderId  order ID
     * @param string $aKeyValue values array
     *
     * @throws DatabaseErrorException
     *
     * @return integer Number of rows affected by the SQL statement
     */
    public function updateOrder($sOrderId, $aKeyValue)
    {
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
        return $this->oDb->Execute(
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
    public function getTransactionById($sTransactionId)
    {
        $transaction = $this->oDb->getRow(
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
    public function deleteTransactionById($sTransactionId)
    {
        return $this->oDb->Execute(
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
    public function getPaymentMethodById($sPaymentId)
    {
        $payment = $this->oDb->getRow(
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
    public function deletePaymentMethodById($sPaymentId)
    {
        return $this->oDb->Execute(
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
    public function updatePaymentMethod($paymentId, $aKeyValue)
    {
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
        return $this->oDb->Execute(
            $sQuery,
            $array
        );
    }

    /**
     * Adds payment in the oxpayments table
     *
     * @param PaymentMethod $oPaymentMethod payment method object
     *
     * @throws DatabaseErrorException
     *
     * @return integer Number of rows affected by the SQL statement
     */
    public function addPaymentMethod($oPaymentMethod)
    {
        return $this->oDb->Execute(
            "INSERT INTO " . self::PAYMENT_TABLE_NAME . " (`OXID`, `OXACTIVE`,
            `OXDESC`, `WDOXIDEE_LOGO`, `WDOXIDEE_TRANSACTIONTYPE`,
            `WDOXIDEE_APIURL`, `WDOXIDEE_MAID`, `WDOXIDEE_SECRET`, `WDOXIDEE_HTTPUSER`,
            `WDOXIDEE_HTTPPASS`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            array(
                $oPaymentMethod->getConfigId(),
                '0',
                $oPaymentMethod->getDescription(),
                $oPaymentMethod->getLogoPath(),
                $oPaymentMethod->getTransactionType(),
                $oPaymentMethod->getApiUrl(),
                $oPaymentMethod->getMaid(),
                $oPaymentMethod->getSecret(),
                $oPaymentMethod->getHttpUser(),
                $oPaymentMethod->getHttpPass()
            )
        );
    }
}
