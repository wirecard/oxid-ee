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
    private $oConfig;
    private $oLocators;
    private $oMockData;

    /**
     * @inheritdoc
     */
    public function __construct($sName = null, $aData = [], $sDataName = '')
    {
        parent::__construct($sName, $aData, $sDataName);

        $this->oConfig = $this->getJsonFromFile(__DIR__ . '/inc/config.json');
        $this->oLocators = $this->getJsonFromFile(__DIR__ . '/inc/locators.json');
        $this->oMockData = $this->getJsonFromFile(__DIR__ . '/inc/mock-data.json');
    }

    /**
     * Parses JSON from a file and returns it if it is valid.
     *
     * @param string $sPath
     *
     * @return array
     */
    private function getJsonFromFile($sPath)
    {
        return json_decode(file_get_contents($sPath), true);
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
     *
     * @inheritdoc
     */
    protected function failOnLoggedExceptions()
    {
    }

    /**
     * Returns a value in an array by a path in dot notation (e.g. "a.b.c").
     *
     * @param array $aArray
     * @param string $sPath
     *
     * @return mixed
     */
    private function getArrayValueByPath($aArray, $sPath)
    {
        $mValue = $aArray;

        foreach (explode('.', $sPath) as $sPathPart) {
            if (!isset($mValue[$sPathPart])) {
                return null;
            }

            $mValue = $mValue[$sPathPart];
        }

        return $mValue;
    }

    /**
     * Returns a config value by path.
     *
     * @param string $sPath
     *
     * @return mixed
     */
    public function getConfig($sPath = null)
    {
        return $sPath ? $this->getArrayValueByPath($this->oConfig, $sPath) : $this->oConfig;
    }

    /**
     * Returns a mock data value by path.
     *
     * @param string $sPath
     *
     * @return mixed
     */
    public function getMockData($sPath = null)
    {
        return $sPath ? $this->getArrayValueByPath($this->oMockData, $sPath) : $this->oMockData;
    }

    /**
     * Returns a locator by path.
     *
     * @param string $sPath
     *
     * @return mixed
     */
    public function getLocator($sPath = null)
    {
        return $sPath ? $this->getArrayValueByPath($this->oLocators, $sPath) : $this->oLocators;
    }

    /**
     * Patches the select method by ignoring an event exception thrown in Selenium RC.
     *
     * @see https://github.com/seleniumhq/selenium-google-code-issue-archive/issues/8184
     * @inheritdoc
     */
    public function select($sSelector, $sOptionSelector)
    {
        try {
            parent::select($sSelector, $sOptionSelector);
        } catch (Exception $oException) {
            if (strpos($oException->getMessage(), 'EventTarget.dispatchEvent') === false) {
                throw $oException;
            }

            $this->fireEvent($sSelector, 'change');
        }
    }

    /**
     * Selects a frame by selector.
     *
     * @param string $sSelector
     */
    public function selectFrameBySelector($sSelector)
    {
        $oElement = $this->getElement($sSelector);
        $sElementName = $oElement->getAttribute('name');

        $this->getMinkSession()->getDriver()->switchToIFrame($sElementName);
        $this->selectedFrame = $sElementName;
    }

    /**
     * Returns a travis environment variable
     *
     * @param string $environmentVariable
     *
     * @return mixed
     */
    public function getEnvironmentVariable($environmentVariable) {
        return getenv($environmentVariable);
    }
}
