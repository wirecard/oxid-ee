<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Core;

use Exception;

use OxidEsales\Eshop\Core\DatabaseProvider;

/**
 * Collection of helper methods to interact with the database.
 *
 * @since 1.2.0
 */
class DatabaseHelper
{
    /**
     * Executes a query if the specified column does not exist in the table.
     *
     * @param string $sTableName  database table name
     * @param string $sColumnName database column name
     * @param string $sQuery      SQL query to execute if column does not exist in the table
     *
     * @return bool true if the query was executed, otherwise false
     *
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     *
     * @since 1.0.0
     */
    public static function addColumnIfNotExists($sTableName, $sColumnName, $sQuery)
    {
        $aColumns = DatabaseProvider::getDb()->getAll("SHOW COLUMNS FROM {$sTableName} LIKE '{$sColumnName}'");

        if (!$aColumns || count($aColumns) === 0) {
            try {
                DatabaseProvider::getDb()->execute($sQuery);

                return true;
            } catch (Exception $oException) {
            }
        }

        return false;
    }

    /**
     * Executes a query if no row with the specified criteria exists in the table.
     *
     * @param string $sTableName database table name
     * @param array  $aKeyValue  associative array to build the "where" query string with
     * @param string $sQuery     SQL query to execute if no row with the search criteria exists in the table
     *
     * @return boolean true if the query was executed, otherwise false
     *
     * @since 1.0.0
     */
    public static function insertRowIfNotExists($sTableName, $aKeyValue, $sQuery)
    {
        $sWhere = '';

        foreach ($aKeyValue as $sKey => $sValue) {
            $sWhere .= " AND `{$sKey}` = '{$sValue}'";
        }

        $sCheckQuery = "SELECT * FROM {$sTableName} WHERE 1{$sWhere}";
        $sExisting = DatabaseProvider::getDb()->getOne($sCheckQuery);

        if (!$sExisting) {
            DatabaseProvider::getDb()->execute($sQuery);

            return true;
        }

        return false;
    }
}
