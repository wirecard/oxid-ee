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
$metadataVersion = '1.0';

/**
 * Module information
 */
$aModule = array(
    'id'                => 'paymentgateway',
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
    'files'             => array(
        // all file paths need to include the actual full installation path of the module
        // in this case 'wirecard/paymentgateway'

        // core
        'OxidEE_Events'         => 'wirecard/paymentgateway/Core/OxidEE_Events.php'
    ),
    'blocks' => array(
        array('template' => 'home.tpl', 'block'=>'admin_home_head', 'file'=>'views/terms_modal.tpl')
    ),
    'events'            => array(
        'onActivate'        => 'OxidEE_Events::onActivate',
        'onDeactivate'      => 'OxidEE_Events::onDeactivate'
    )
);
