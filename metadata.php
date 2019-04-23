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
    'title'             => 'Wirecard OXID Module',
    'description'       => array(
        'de' => $aModuleDescriptions['de'],
        'en' => $aModuleDescriptions['en']
    ),
    'lang'              => 'en',
    'thumbnail'         => 'logo.png',
    'version'           => '0.1.0',
    'author'            => 'Wirecard',
    'url'               => 'https://www.wirecard.com',
    'email'             => 'shop-systems-support@wirecard.com',
    'extend'            => array (
        \OxidEsales\Eshop\Core\ViewConfig::class
            => \Wirecard\Oxid\Extend\View_Config::class,
        \OxidEsales\Eshop\Application\Controller\Admin\OrderList::class
            => \Wirecard\Oxid\Extend\Controller\Admin\OrderList::class,
        \OxidEsales\Eshop\Application\Model\Order::class
            => \Wirecard\Oxid\Extend\Model\Order::class,
        \OxidEsales\Eshop\Application\Controller\OrderController::class
            => \Wirecard\Oxid\Extend\Controller\OrderController::class,
        \OxidEsales\Eshop\Application\Model\Payment::class
            => \Wirecard\Oxid\Extend\Model\Payment::class,
        \OxidEsales\Eshop\Application\Controller\PaymentController::class
            => \Wirecard\Oxid\Extend\Controller\PaymentController::class,
        \OxidEsales\Eshop\Application\Model\PaymentGateway::class
            => \Wirecard\Oxid\Extend\Model\Payment_Gateway::class,
        \OxidEsales\Eshop\Application\Controller\Admin\PaymentMainAjax::class
            => \Wirecard\Oxid\Extend\Payment_Main_Ajax::class,
        \OxidEsales\Eshop\Application\Model\Basket::class
            => \Wirecard\Oxid\Extend\Model\Basket::class,
        \OxidEsales\Eshop\Application\Controller\ThankYouController::class
            => \Wirecard\Oxid\Extend\Controller\ThankYouController::class
    ),
    'controllers'       => array(
        'wcpg_transaction'
            => \Wirecard\Oxid\Controller\Admin\TransactionController::class,
        'wcpg_transaction_list'
            => \Wirecard\Oxid\Controller\Admin\TransactionList::class,
        'wcpg_transaction_payment_details'
            => \Wirecard\Oxid\Controller\Admin\TransactionTabPaymentDetails::class,
        'wcpg_transaction_transaction_details'
            => \Wirecard\Oxid\Controller\Admin\TransactionTabTransactionDetails::class,
        'wcpg_transaction_account_holder'
            => \Wirecard\Oxid\Controller\Admin\TransactionTabAccountHolder::class,
        'wcpg_transaction_shipping'
            => \Wirecard\Oxid\Controller\Admin\TransactionTabShipping::class,
        'wcpg_transaction_post_processing'
            => \Wirecard\Oxid\Controller\Admin\TransactionTabPostProcessing::class,
        'wcpg_order_transactions'
            => \Wirecard\Oxid\Controller\Admin\OrderTabTransactions::class,
        'wcpg_order_transaction_details'
            => \Wirecard\Oxid\Controller\Admin\OrderTabTransactionDetails::class,
        'wcpg_notifyhandler'
            => \Wirecard\Oxid\Controller\NotifyHandler::class,
        'wcpg_transaction_response_details'
            => \Wirecard\Oxid\Controller\Admin\TransactionTabResponseDetails::class,
    ),
    'blocks' => array(
        array(
            'template' => 'payment_main.tpl',
            'block' => 'admin_payment_main_form',
            'file' => 'out/blocks/wd_admin_payment_main_form.tpl'
        ),
        array(
            'template' => 'order_list.tpl',
            'block' => 'admin_order_list_colgroup',
            'file' => 'out/blocks/wd_admin_order_list_colgroup.tpl',
            'position' => 10,
        ),
        array(
            'template' => 'order_list.tpl',
            'block' => 'admin_order_list_filter',
            'file' => 'out/blocks/wd_admin_order_list_filter.tpl',
            'position' => 10,
        ),
        array(
            'template' => 'order_list.tpl',
            'block' => 'admin_order_list_sorting',
            'file' => 'out/blocks/wd_admin_order_list_sorting.tpl',
            'position' => 10,
        ),
        array(
            'template' => 'order_list.tpl',
            'block' => 'admin_order_list_item',
            'file' => 'out/blocks/wd_admin_order_list_item.tpl',
            'position' => 10,
        ),
        array(
            'template' => 'page/checkout/order.tpl',
            'block' => 'checkout_order_main',
            'file' => 'out/blocks/profiling_tags.tpl'
        ),
        array(
            'template' => 'page/checkout/payment.tpl',
            'block' => 'checkout_payment_errors',
            'file' => 'out/blocks/checkout_errors.tpl'
        ),
        array(
            'template' => 'page/checkout/thankyou.tpl',
            'block' => 'checkout_thankyou_info',
            'file' => 'views/blocks/thankyou.tpl'
        ),
        array(
            'template' => 'page/account/order.tpl',
            'block' => 'account_order_history_cart_items',
            'file' => 'views/blocks/accountorder.tpl'
        ),
        array(
            'theme' => 'azure-theme',
            'template' => 'page/account/order.tpl',
            'block' => 'account_order_persparams',
            'file' => 'views/blocks/accountorder_azure.tpl'
        ),
        array(
            'template' => 'page/checkout/inc/payment_other.tpl',
            'block' => 'checkout_payment_longdesc',
            'file' => 'views/blocks/checkout_payment_longdesc.tpl'
        )
    ),
    'templates'         => array(
        'transaction.tpl'                   => 'wirecard/paymentgateway/views/admin/tpl/transaction.tpl',
        'transaction_list.tpl'              => 'wirecard/paymentgateway/views/admin/tpl/transaction_list.tpl',
        'tab_simple.tpl'                    => 'wirecard/paymentgateway/views/admin/tpl/tab_simple.tpl',
        'tab_table.tpl'                     => 'wirecard/paymentgateway/views/admin/tpl/tab_table.tpl',
        'tab_post_processing.tpl'           => 'wirecard/paymentgateway/views/admin/tpl/tab_post_processing.tpl'
    ),
    'events'            => array(
        'onActivate'        => '\Wirecard\Oxid\Core\OxidEE_Events::onActivate',
        'onDeactivate'      => '\Wirecard\Oxid\Core\OxidEE_Events::onDeactivate'
    )
);
