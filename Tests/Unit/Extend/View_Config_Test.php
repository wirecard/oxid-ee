<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 *
 */

use \Wirecard\Oxid\Extend\View_Config;

class View_Config_Test extends OxidEsales\TestingLibrary\UnitTestCase
{

    /**
     * @var \Wirecard\Oxid\Extend\View_Config
     */
    private $oViewConfig;

    protected function setUp()
    {
        parent::setUp();
        $this->oViewConfig = oxNew(View_Config::class);
    }

    public function testModuleDeviceId()
    {
        $sMaid = "test Merchant Id";
        $this->assertTrue(strpos($this->oViewConfig->getModuleDeviceId($sMaid), $sMaid) === 0);
    }
}
