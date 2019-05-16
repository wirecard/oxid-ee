<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

use Selenium\Exception;

/**
 * Basic acceptance test class to be used by all acceptance tests.
 */
abstract class BaseAcceptanceTestCase extends \OxidEsales\TestingLibrary\AcceptanceTestCase
{
    private $config;
    private $locators;

    public function __construct($name = null, $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->config = require __DIR__ . '/inc/config.php';
        $this->locators = require __DIR__ . '/inc/locators.php';
    }

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->activateTheme('azure');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        parent::tearDown();

        // the session has to be closed to get a fresh browser window
        self::stopMinkSession();
    }

    /**
     * Do not fail tests on log messages.
     * @inheritdoc
     */
    protected function failOnLoggedExceptions()
    {
    }

    /**
     * Returns a value in an array by a path in dot notation (e.g. "a.b.c").
     * @param array $array
     * @param string $path
     * @return mixed
     */
    private function getArrayValueByPath($array, $path)
    {
        $value = $array;

        foreach (explode('.', $path) as $pathPart) {
            if (!isset($value[$pathPart])) {
                return null;
            }

            $value = $value[$pathPart];
        }

        return $value;
    }

    /**
     * Returns a config value by path.
     * @param string $path
     * @return mixed
     */
    public function getConfigValue($path)
    {
        return $this->getArrayValueByPath($this->config, $path);
    }

    /**
     * Returns a locator by path.
     * @param string $path
     * @return mixed
     */
    public function getLocator($path)
    {
        return $this->getArrayValueByPath($this->locators, $path);
    }

    /**
     * Patches the select method by ignoring an event exception thrown in Selenium RC.
     * @see https://github.com/seleniumhq/selenium-google-code-issue-archive/issues/8184
     * @inheritdoc
     */
    public function select($selector, $optionSelector)
    {
        try {
            parent::select($selector, $optionSelector);
        } catch (Exception $exception) {
            if (strpos($exception->getMessage(), 'EventTarget.dispatchEvent') === false) {
                throw $exception;
            }
        }
    }
}
