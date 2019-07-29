<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\resources;

class OrderHelperData
{
    const DB_DATA = [
        [
            'table' => 'oxuser',
            'columns' => [
                'OXID',
                'OXACTIVE',
                'OXUSERNAME',
                'OXFNAME',
                'OXLNAME',
                'OXCOMPANY',
                'OXSTREET',
                'OXSTREETNR',
                'OXZIP',
                'OXCITY',
                'OXCOUNTRYID',
                'OXSTATEID',
            ],
            'rows' => [
                [
                    'User ID 1',
                    1,
                    'User 1',
                    'User',
                    'One',
                    'Red Company',
                    'Blue Square',
                    '1',
                    '5555',
                    'Green City',
                    '1',
                    '1'
                ],
                [
                    'User ID 2',
                    1,
                    'User 2',
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null
                ],
            ],
        ],
        [
            'table' => 'oxaddress',
            'columns' => [
                'OXID',
                'OXUSERID',
                'OXFNAME',
                'OXLNAME',
                'OXCOMPANY',
                'OXSTREET',
                'OXSTREETNR',
                'OXZIP',
                'OXCITY',
                'OXCOUNTRYID',
                'OXSTATEID',
            ],
            'rows' => [
                [
                    '1',
                    'User ID 1',
                    'First',
                    'Last',
                    'Company',
                    'Street',
                    '10',
                    '9876',
                    'City',
                    '9',
                    '9',
                ],
            ],
        ],
        [
            'table' => 'oxcountry',
            'columns' => [
                'OXID',
                'OXTITLE',
            ],
            'rows' => [
                [
                    '9',
                    'Testland',
                ],
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

    const TEST_USER_1 = [
        'first_name' => 'Oxid',
        'last_name' => 'User',
        'company' => 'ACME',
        'street' => 'Strasse',
        'street_nr' => '1',
        'zip' => '1234',
        'city' => 'Stadt',
        'country_id' => '1',
        'state_id' => '1',
    ];

    const TEST_USER_2 = [
        'first_name' => 'User',
        'last_name' => 'Oxid',
        'company' => 'Firma',
        'street' => 'Platz',
        'street_nr' => '2',
        'zip' => '44567',
        'city' => 'Großstadt',
        'country_id' => '1',
        'state_id' => '1',
    ];

    const TEST_USER_3 = [
        'first_name' => 'First',
        'last_name' => 'Last',
        'company' => 'Company',
        'street' => 'Street',
        'street_nr' => '10',
        'zip' => '9876',
        'city' => 'City',
        'country_id' => '9',
        'state_id' => '9',
    ];

    const TEST_USER_4 = [
        'first_name' => 'User',
        'last_name' => 'One',
        'company' => 'Red Company',
        'street' => 'Blue Square',
        'street_nr' => '1',
        'zip' => '5555',
        'city' => 'Green City',
        'country_id' => '1',
        'state_id' => '1',
    ];
}
