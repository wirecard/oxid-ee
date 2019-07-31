<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Test;

use OxidEsales\TestingLibrary\UnitTestCase;

use OxidEsales\Eshop\Core\DatabaseProvider;

use Psr\Log\LogLevel;
use PHPUnit_Framework_TestResult;
use oxTestModules;

/**
 * UnitTextCase extension that allows setting database values from within the test.
 */
abstract class WdUnitTestCase extends UnitTestCase
{
    const FAIL_TEST_ON_LOG_LEVELS = [
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
    ];

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->removeDbData();
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
     * Only fails a test if the log level matches one of FAIL_TEST_ON_LOG_LEVELS.
     *
     * @inheritdoc
     */
    protected function failOnLoggedExceptions()
    {
        $logFileContent = $this->exceptionLogHelper->getExceptionLogFileContent();

        if ($logFileContent) {
            if (in_array($this->getLogLevelFromLogFileContent($logFileContent), self::FAIL_TEST_ON_LOG_LEVELS)) {
                parent::failOnLoggedExceptions();
            } else {
                $this->exceptionLogHelper->clearExceptionLogFile();
            }
        }
    }
    /**
     * Returns the log level of the first log entry in a given log file content.
     *
     * @param string $logFileContent
     * @return string|null
     */
    protected function getLogLevelFromLogFileContent($logFileContent)
    {
        preg_match('/Logger\.\K[A-Z]+/', $logFileContent, $matches);

        if ($logFileContent) {
            return constant(LogLevel::class . "::{$matches[0]}");
        }

        return null;
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
