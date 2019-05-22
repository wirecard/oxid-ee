<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 *
 */

use Wirecard\Oxid\Extend\Language;

class LanguageTest extends OxidEsales\TestingLibrary\UnitTestCase
{

    /**
     * @var Wirecard\Oxid\Extend\Language
     */
    private $oLanguage;

    protected function setUp()
    {
        parent::setUp();
        $this->oLanguage = oxNew(Language::class);
    }

    /**
     * @dataProvider translateStringProvider
     */
    public function testTranslateString($sKey, $sTranslation, $iLanguageId)
    {
        $this->setLanguage(1);
        $this->assertEquals($sTranslation, $this->oLanguage->translateString($sKey, $iLanguageId));
    }

    public function translateStringProvider()
    {
        return [
            'default translation' => ['wd_yes', 'Yes', null],
            'default german translation' => ['wd_yes', 'Ja', 0],
            'unknown translation' => ['wd_unknown_key', 'wd_unknown_key', null],
            'unknown language' => ['wd_yes', 'Yes', 99],
        ];
    }
}
