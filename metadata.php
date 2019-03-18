<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

/**
 * Metadata version
 */
$sMetadataVersion = '2.1';

// method to toggle the visibility of the DOM elements for the module terms and conditions content
$sToggleTermsJs = file_get_contents(dirname(__FILE__) . '/out/html/toggle-terms-of-use-css-js.html');

// currently, the terms of use only exist in English and are thus used for both English and German
$sTermsContentEn = file_get_contents(dirname(__FILE__) . '/out/html/terms-of-use.en.html');
$sTermsContentDe = $sTermsContentEn;

// module description blocks in English and German
$sModuleDescriptionEn = file_get_contents(dirname(__FILE__) . '/out/html/module-description.en.html');
$sModuleDescriptionDe = file_get_contents(dirname(__FILE__) . '/out/html/module-description.de.html');

// the array contains the complete description HTML string for each language
$sTermsContentPlaceholder = '{{ TERMS_CONTENT }}';
$aModuleDescriptions = array(
    'de' => $sToggleTermsJs . str_replace($sTermsContentPlaceholder, $sTermsContentDe, $sModuleDescriptionDe),
    'en' => $sToggleTermsJs . str_replace($sTermsContentPlaceholder, $sTermsContentEn, $sModuleDescriptionEn)
);

/**
 * Module information
 */
$aModule = array(
    'id'                => 'wdoxidee',
    'title'             => 'Wirecard Oxid EE Paymentgateway',
    'description'       => array(
        'de' => $aModuleDescriptions['de'],
        'en' => $aModuleDescriptions['en']
    ),
    'lang'              => 'en',
    'thumbnail'         => 'wirecard-logo.png',
    'version'           => '1.0.0',
    'author'            => 'Wirecard',
    'url'               => 'https://www.wirecard.com',
    'email'             => 'support.at@wirecard.com',
    'extend'            => array (
        \OxidEsales\Eshop\Core\ViewConfig::class                  => \Wirecard\Oxid\Extend\ViewConfig::class,
        \OxidEsales\Eshop\Application\Model\Order::class          => \Wirecard\Oxid\Extend\Order::class,
        \OxidEsales\Eshop\Application\Model\PaymentGateway::class => \Wirecard\Oxid\Extend\Payment_Gateway::class
    ),
    'blocks' => array(
        array(
            'template' => 'payment_main.tpl',
            'block' => 'admin_payment_main_form',
            'file' => 'out/blocks/wd_admin_payment_main_form.tpl'
        ),
        array(
            'template' => 'page/checkout/order.tpl',
            'block' => 'checkout_order_main',
            'file' => 'out/blocks/profiling_tags.tpl'
        )
    ),
    'events'            => array(
        'onActivate'        => '\Wirecard\Oxid\Core\OxidEE_Events::onActivate',
        'onDeactivate'      => '\Wirecard\Oxid\Core\OxidEE_Events::onDeactivate'
    )
);
