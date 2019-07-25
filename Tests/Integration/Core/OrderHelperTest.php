<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Core\OrderHelper;

class OrderHelperTest extends \Wirecard\Test\WdUnitTestCase
{
    protected function dbData()
    {
        return [
            [
                'table' => 'oxuser',
                'columns' => ['OXID', 'OXACTIVE', 'OXUSERNAME'],
                'rows' => [
                    ['User ID 1', 1, 'User 1'],
                    ['User ID 2', 1, 'User 2'],
                ],
            ],
            [
                'table' => 'oxorder',
                'columns' => [
                    'OXID',
                    'OXUSERID',
                    'OXORDERDATE',
                    'OXDELFNAME',
                    'OXDELLNAME',
                    'OXDELCOMPANY',
                    'OXDELSTREET',
                    'OXDELSTREETNR',
                    'OXDELZIP',
                    'OXDELCITY',
                    'OXDELCOUNTRYID',
                    'OXDELSTATEID',
                    'OXBILLFNAME',
                    'OXBILLLNAME',
                    'OXBILLCOMPANY',
                    'OXBILLSTREET',
                    'OXBILLSTREETNR',
                    'OXBILLZIP',
                    'OXBILLCITY',
                    'OXBILLCOUNTRYID',
                    'OXBILLSTATEID',
                ],
                'rows' => [
                    [
                        1,
                        'User ID 1',
                        '2019-03-30 10:55:13',
                        'Oxid',
                        'User',
                        'ACME',
                        'Strasse',
                        '1',
                        '1234',
                        'Stadt',
                        '1',
                        '1',
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                    ],
                    [
                        2,
                        'User ID 2',
                        '2019-03-29 10:55:13',
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        'User',
                        'Oxid',
                        'Firma',
                        'Platz',
                        '2',
                        '44567',
                        'Großstadt',
                        '1',
                        '1',
                    ],
                ],
            ],
        ];
    }


    /**
     * @dataProvider getLastOrderShippingAddressProvider
     */
    public function testGetLastOrderShippingAddress($sUserId, $aExpected)
    {
        $this->assertEquals($aExpected, OrderHelper::getLastOrderShippingAddress($sUserId));
    }

    public function getLastOrderShippingAddressProvider()
    {
        return [
            'unknown user' => ['invalid user', null],
            'order with delivery country set' => [
                'User ID 1',
                [
                    'first_name' => 'Oxid',
                    'last_name' => 'User',
                    'company' => 'ACME',
                    'street' => 'Strasse',
                    'street_nr' => '1',
                    'zip' => '1234',
                    'city' => 'Stadt',
                    'country_id' => '1',
                    'state_id' => '1',
                ],
            ],
            'user without delivery country set' => [
                'User ID 2',
                [
                    'first_name' => 'User',
                    'last_name' => 'Oxid',
                    'company' => 'Firma',
                    'street' => 'Platz',
                    'street_nr' => '2',
                    'zip' => '44567',
                    'city' => 'Großstadt',
                    'country_id' => '1',
                    'state_id' => '1',
                ],
            ],
        ];
    }
}
