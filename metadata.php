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
$sToggleJs = '<script type="text/javascript">
                // initial state is collapsed
                var wdTermsCollapseState = "collapse";

                var wdToggleTermsVisibility = function() {
                    // ids of the DOM elements in collapsed state
                    var ITEMS_COLLAPSE_STATE = ["wd-terms-show-link"];

                    // ids of the DOM elements in expanded state
                    var ITEMS_EXPAND_STATE = ["wd-terms-hide-link", "wd-terms-content"];

                    if (wdTermsCollapseState === "collapse") {
                        ITEMS_COLLAPSE_STATE.forEach(function(elemId) {
                            document.getElementById(elemId).style.display = "none";
                        });

                        ITEMS_EXPAND_STATE.forEach(function(elemId) {
                            document.getElementById(elemId).style.display = "inline-block";
                        });

                        wdTermsCollapseState = "expand";
                    } else {
                        ITEMS_EXPAND_STATE.forEach(function(elemId) {
                            document.getElementById(elemId).style.display = "none";
                        });

                        ITEMS_COLLAPSE_STATE.forEach(function(elemId) {
                            document.getElementById(elemId).style.display = "inline-block";
                        });

                        wdTermsCollapseState = "collapse";
                    }
                }
            </script>';

$sTermsContent = "
    <p>The plugins offered are provided free of charge by Wirecard AG (abbreviated to Wirecard) and are explicitly not part of the Wirecard range of products and services.</p>
    <p>They have been tested and approved for full functionality in the standard configuration (status on delivery) of the corresponding shop system. They are under General Public License Version 3 (GPLv3) and can be used, developed and passed on to third parties under the same terms.</p>
    <p>However, Wirecard does not provide any guarantee or accept any liability for any errors occurring when used in an enhanced, customized shop system configuration.</p>
    <p>Operation in an enhanced, customized configuration is at your own risk and requires a comprehensive test phase by the user of the plugin.</p>
    <p>Customers use the plugins at their own risk. Wirecard does not guarantee their full functionality neither does Wirecard assume liability for any disadvantages related to the use of the plugins. Additionally, Wirecard does not guarantee the full functionality for customized shop systems or installed plugins of  other vendors of plugins within the same shop system.</p>
    <p>Customers are responsible for testing the plugin's functionality before starting productive operation.</p>
    <p>By installing the plugin into the shop system the customer agrees to these terms of use. Please do not use the plugin if you do not agree to these terms of use!</p>
    <p>Uninstalling the plugin may result in the loss of data.</p>
    <h3>Legal notice</h3>
    <p>Wirecard will only be made liable for specifications and functions as described within this documentation.</p>
    <p>No warranty whatsoever can be granted on any alterations and/or new implementations as well as resulting diverging usage not supported or described within this documentation.</p>
";

$aTermsAndConditions = array(
    'de' => $sToggleJs . '<p style="font-weight: bold;">Modul für Zahlung mit Wirecard paymentSDK</p>
            <p>
                Terms and conditions
                <a id="wd-terms-show-link" style="color: blue;" href="#" onclick="wdToggleTermsVisibility()">(show)</a>
                <a id="wd-terms-hide-link" style="color: blue; display: none;" href="#" onclick="wdToggleTermsVisibility()">(hide)</a>
            </p>
            <div id="wd-terms-content" style="display: none;">' . $sTermsContent . '</div>',
    'en' => $sToggleJs . '<p style="font-weight: bold;">Module for payment with Wirecard paymentSDK</p>
            <p>
                Terms and conditions <a id="wd-terms-show-link" style="color: blue;" href="#" onclick="wdToggleTermsVisibility()">(show)</a>
                <a id="wd-terms-hide-link" style="color: blue; display: none;" href="#" onclick="wdToggleTermsVisibility()">(hide)</a>
            </p>
            <div id="wd-terms-content" style="display: none;">' . $sTermsContent . '</div>'
);

/**
 * Module information
 */
$aModule = array(
    'id'                => 'paymentgateway',
    'title'             => 'Wirecard Oxid EE Paymentgateway',
    'description'       => array(
        'de' => $aTermsAndConditions['de'],
        'en' => $aTermsAndConditions['en']
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
