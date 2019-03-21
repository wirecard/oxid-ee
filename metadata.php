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

/**
 * Module information
 */
$aModule = array(
    'id'                => 'wdoxidee',
    'title'             => 'Wirecard Oxid EE Paymentgateway',
    'description'       => array(
        'de' => 'Modul für Zahlung mit Wirecard paymentSDK',
        'en' => 'Module for payment with Wirecard paymentSDK'
    ),
    'lang'              => 'en',
    'thumbnail'         => 'wirecard-logo.png',
    'version'           => '1.0.0',
    'author'            => 'Wirecard',
    'url'               => 'https://www.wirecard.com',
    'email'             => 'support.at@wirecard.com',
    'extend'            => array (
        \OxidEsales\Eshop\Application\Model\Order::class          => \Wirecard\Oxid\Extend\Order::class,
        \OxidEsales\Eshop\Application\Model\PaymentGateway::class => \Wirecard\Oxid\Extend\Payment_Gateway::class
    ),
    'blocks' => array(
        array(
            'template' => 'home.tpl',
            'block'=>'admin_home_head',
            'file'=>'views/terms_modal.tpl'
        ),
        array(
            'template' => 'payment_main.tpl',
            'block' => 'admin_payment_main_form',
            'file' => 'out/blocks/wd_admin_payment_main_form.tpl'
        )
    ),
    'events'            => array(
        'onActivate'        => '\Wirecard\Oxid\Core\OxidEE_Events::onActivate',
        'onDeactivate'      => '\Wirecard\Oxid\Core\OxidEE_Events::onDeactivate'
    )
);
