<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Core\DatabaseHelper;

use Wirecard\Test\WdUnitTestCase;

class DatabaseHelperTest extends WdUnitTestCase
{
    /**
     * @dataProvider addColumnIfNotExistsProvider
     */
    public function testAddColumnIfNotExists($sTableName, $sColumnName, $bSuccess)
    {
        $bResponse = DatabaseHelper::addColumnIfNotExists($sTableName, $sColumnName, 'ALTER TABLE `oxarticles`');

        $this->assertEquals($bSuccess, $bResponse);
    }

    public function addColumnIfNotExistsProvider()
    {
        return [
            'existing column' => [
                'oxarticles',
                'oxtitle',
                false,
            ],
            'non-existing column' => [
                'oxarticles',
                'foo',
                true,
            ],
        ];
    }

    /**
     * @dataProvider insertRowIfNotExistsProvider
     */
    public function testInsertRowIfNotExists($sTableName, $aKeyValue, $bSuccess)
    {
        $bResponse = DatabaseHelper::insertRowIfNotExists($sTableName, $aKeyValue, 'ALTER TABLE `oxarticles`');

        $this->assertEquals($bSuccess, $bResponse);
    }

    public function insertRowIfNotExistsProvider()
    {
        return [
            'existing row' => [
                'oxpayments',
                ['OXID' => 'oxidinvoice'],
                false,
            ],
            'non-existing row' => [
                'oxpayments',
                ['OXID' => 'foo'],
                true,
            ],
        ];
    }
}
