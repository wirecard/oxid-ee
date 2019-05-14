<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

/**
 * Basic acceptance test class to be used by all acceptance tests.
 */
abstract class BaseAcceptanceTestCase extends \OxidEsales\TestingLibrary\AcceptanceTestCase
{
    private $config;

    public function __construct($name = null, $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->config = require_once(__DIR__ . '/config.php');
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
     * Do not fail tests on log messages.
     * @inheritdoc
     */
    protected function failOnLoggedExceptions()
    {
    }

    /**
     * Returns a config value by a path in dot notation.
     * @param string $path
     */
    public function getConfigValue($path)
    {
        $value = $this->config;

        foreach (explode('.', $path) as $pathPart) {
            if (!isset($value[$pathPart])) {
                return null;
            }

            $value = $value[$pathPart];
        }

        return $value;
    }
}
