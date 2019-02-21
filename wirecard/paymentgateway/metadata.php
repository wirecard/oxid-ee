<?php

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
    'email'             => 'developer.center@wirecard.com',
    'files'             => array(
        // core
        'OxidEE_Events'         => 'paymentgateway/Core/OxidEE_Events.php'
    ),
    'blocks' => array(
        array('template' => 'home.tpl', 'block'=>'admin_home_head', 'file'=>'application/views/terms_modal.tpl')
    ),
    'events'            => array(
        'onActivate'        => 'OxidEE_Events::onActivate',
        'onDeactivate'      => 'OxidEE_Events::onDeactivate'
    )
);