<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */
 
namespace Wirecard\Oxid\Tests\Acceptance;

abstract class BaseAcceptanceTestCase extends \OxidEsales\TestingLibrary\AcceptanceTestCase
{
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
}
