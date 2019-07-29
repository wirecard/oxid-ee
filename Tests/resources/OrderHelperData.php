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
    const USER_DELIVERY_COUNTRY = [
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

    const USER_NO_DELIVERY_COUNTRY = [
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

    const USER_SHIPPING_INVALID = [
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

    const USER_SHIPPING_VALID = [
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
                    self::USER_SHIPPING_VALID['first_name'],
                    self::USER_SHIPPING_VALID['last_name'],
                    self::USER_SHIPPING_VALID['company'],
                    self::USER_SHIPPING_VALID['street'],
                    self::USER_SHIPPING_VALID['street_nr'],
                    self::USER_SHIPPING_VALID['zip'],
                    self::USER_SHIPPING_VALID['city'],
                    self::USER_SHIPPING_VALID['country_id'],
                    self::USER_SHIPPING_VALID['state_id'],
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
                    self::USER_SHIPPING_INVALID['first_name'],
                    self::USER_SHIPPING_INVALID['last_name'],
                    self::USER_SHIPPING_INVALID['company'],
                    self::USER_SHIPPING_INVALID['street'],
                    self::USER_SHIPPING_INVALID['street_nr'],
                    self::USER_SHIPPING_INVALID['zip'],
                    self::USER_SHIPPING_INVALID['city'],
                    self::USER_SHIPPING_INVALID['country_id'],
                    self::USER_SHIPPING_INVALID['state_id'],
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
                    self::USER_DELIVERY_COUNTRY['first_name'],
                    self::USER_DELIVERY_COUNTRY['last_name'],
                    self::USER_DELIVERY_COUNTRY['company'],
                    self::USER_DELIVERY_COUNTRY['street'],
                    self::USER_DELIVERY_COUNTRY['street_nr'],
                    self::USER_DELIVERY_COUNTRY['zip'],
                    self::USER_DELIVERY_COUNTRY['city'],
                    self::USER_DELIVERY_COUNTRY['country_id'],
                    self::USER_DELIVERY_COUNTRY['state_id'],
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
                    self::USER_NO_DELIVERY_COUNTRY['first_name'],
                    self::USER_NO_DELIVERY_COUNTRY['last_name'],
                    self::USER_NO_DELIVERY_COUNTRY['company'],
                    self::USER_NO_DELIVERY_COUNTRY['street'],
                    self::USER_NO_DELIVERY_COUNTRY['street_nr'],
                    self::USER_NO_DELIVERY_COUNTRY['zip'],
                    self::USER_NO_DELIVERY_COUNTRY['city'],
                    self::USER_NO_DELIVERY_COUNTRY['country_id'],
                    self::USER_NO_DELIVERY_COUNTRY['state_id'],
                ],
            ],
        ],
    ];
}
