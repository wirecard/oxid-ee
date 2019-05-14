<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

/**
 * Contains all locators used in the tests.
 */
return [
    'checkout' => [
        'nextStep' => '//button[contains(@class, "nextStep")]',
        'paymentMethods' => [
            'paypal' => 'payment_wdpaypal',
        ],
    ],
    'external' => [
        'paypal' => [
            'loginEmail' => 'email',
            'loginPassword' => 'password',
            'loginButton' => 'btnLogin',
            'buyNowButton' => 'confirmButtonTop',
        ],
    ],
];
