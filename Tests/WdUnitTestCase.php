<?php

namespace Wirecard\Test;

use OxidEsales\TestingLibrary\UnitTestCase;

use OxidEsales\Eshop\Core\DatabaseProvider;

/**
 * UnitTextCase extension that allows setting database values from within the test.
 */
abstract class WdUnitTestCase extends UnitTestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->addDbData();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->removeDbData();
    }

    /**
     * Adds data specified in `dbData` to the database.
     *
     * @return void
     */
    protected function addDbData()
    {
        if (!$this->dbData()) {
            return;
        }

        $db = DatabaseProvider::getDb();

        foreach ($this->dbData() as $dbDataSet) {
            if (!$this->isValidDbDataSet($dbDataSet)) {
                continue;
            }

            $queryTable = $db->quoteIdentifier($dbDataSet['table']);
            $queryColumns = implode(', ', array_map(function ($column) use ($db) {
                return $db->quoteIdentifier($column);
            }, $dbDataSet['columns']));

            foreach ($dbDataSet['rows'] as $row) {
                $queryValues = implode(', ', $db->quoteArray($row));

                $db->execute("INSERT INTO {$queryTable} ({$queryColumns}) VALUES ({$queryValues})");
            }
        }
    }

    /**
     * Removes data specified in `dbData` from the database.
     *
     * @return void
     */
    protected function removeDbData()
    {
        if (!$this->dbData()) {
            return;
        }

        $db = DatabaseProvider::getDb();

        foreach ($this->dbData() as $dbDataSet) {
            if (!$this->isValidDbDataSet($dbDataSet)) {
                continue;
            }

            $queryTable = $db->quoteIdentifier($dbDataSet['table']);

            $db->execute("TRUNCATE TABLE {$queryTable}");
        }
    }

    /**
     * Defines data to be put into the database on setUp. This must return a multidimensional array containing one
     * entry for each affected table, e.g.:
     *
     * ```
     * [
     *     [
     *         'table' => 'oxorder',
     *         'columns' => ['oxid', 'oxuserid', 'oxordernr'],
     *         'rows' => [
     *             ['oxid 1', 'oxuserid 1', 'oxordernr 1'],
     *             ['oxid 2', 'oxuserid 2', 'oxordernr 2'],
     *             ['oxid 3', 'oxuserid 3', 'oxordernr 3'],
     *         ],
     *     ],
     * ]
     * ```
     * @return array
     */
    protected function dbData()
    {
        return [];
    }

    /**
     * Checks if a given database data set is valid.
     *
     * @param array $dbDataSet
     * @return bool
     */
    protected function isValidDbDataSet(array $dbDataSet): bool
    {
        return !empty($dbDataSet['table']) && !empty($dbDataSet['columns']) && !empty($dbDataSet['rows']);
    }
}
