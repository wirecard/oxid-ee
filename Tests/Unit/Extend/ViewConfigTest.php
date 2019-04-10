<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 *
 */

use \Wirecard\Oxid\Extend\ViewConfig;

class ViewConfigTest extends OxidEsales\TestingLibrary\UnitTestCase
{

    /**
     * @var \Wirecard\Oxid\Extend\ViewConfig
     */
    private $oViewConfig;

    protected function setUp()
    {
        parent::setUp();
        $this->oViewConfig = oxNew(ViewConfig::class);
    }

    public function testModuleDeviceId()
    {
        $sMaid = "test Merchant Id";
        $this->assertTrue(strpos($this->oViewConfig->getModuleDeviceId($sMaid), $sMaid) === 0);
    }
}
