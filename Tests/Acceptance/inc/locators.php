<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

/**
 * Contains a map for UI locators.
 * @see https://www.seleniumhq.org/docs/06_test_design_considerations.jsp#user-interface-mapping
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
            'email' => 'email',
            'password' => 'password',
            'login' => 'btnLogin',
            'nextStep' => '//*[contains(@class, "continueButton")]',
        ],
    ],
];
