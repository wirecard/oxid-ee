<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Core\Helper;

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
$sTermsContentSnippet = '{{ TERMS_CONTENT }}';
$aModuleDescriptions = [
    'de' => $sToggleTermsJs . str_replace($sTermsContentSnippet, $sTermsContentDe, $sModuleDescriptionDe),
    'en' => $sToggleTermsJs . str_replace($sTermsContentSnippet, $sTermsContentEn, $sModuleDescriptionEn),
];

/**
 * Module information
 */
$aModule = [
    'id'                => Helper::MODULE_ID,
    'title'             => 'Wirecard OXID Module',
    'description'       => [
        'de' => $aModuleDescriptions['de'],
        'en' => $aModuleDescriptions['en'],
    ],
    'lang'              => 'en',
    'thumbnail'         => 'logo.png',
    'version'           => '1.1.0',
    'author'            => 'Wirecard',
    'url'               => 'https://www.wirecard.com',
    'email'             => 'shop-systems-support@wirecard.com',
    'extend'            => [
        \OxidEsales\Eshop\Core\Language::class
            => \Wirecard\Oxid\Extend\Language::class,
        \OxidEsales\Eshop\Core\ViewConfig::class
            => \Wirecard\Oxid\Extend\ViewConfig::class,
        \OxidEsales\Eshop\Application\Controller\Admin\OrderList::class
            => \Wirecard\Oxid\Extend\Controller\Admin\OrderList::class,
        \OxidEsales\Eshop\Application\Controller\Admin\PaymentMain::class
            => \Wirecard\Oxid\Extend\Controller\Admin\PaymentMain::class,
        \OxidEsales\Eshop\Application\Model\Order::class
            => \Wirecard\Oxid\Extend\Model\Order::class,
        \OxidEsales\Eshop\Application\Controller\OrderController::class
            => \Wirecard\Oxid\Extend\Controller\OrderController::class,
        \OxidEsales\Eshop\Application\Controller\PaymentController::class
            => \Wirecard\Oxid\Extend\Controller\PaymentController::class,
        \OxidEsales\Eshop\Application\Model\Payment::class
            => \Wirecard\Oxid\Extend\Model\Payment::class,
        \OxidEsales\Eshop\Application\Controller\Admin\PaymentMainAjax::class
            => \Wirecard\Oxid\Extend\PaymentMainAjax::class,
        \OxidEsales\Eshop\Application\Model\Basket::class
            => \Wirecard\Oxid\Extend\Model\Basket::class,
        \OxidEsales\Eshop\Application\Controller\ThankYouController::class
            => \Wirecard\Oxid\Extend\Controller\ThankYouController::class,
        \OxidEsales\Eshop\Core\Email::class
            => \Wirecard\Oxid\Extend\Core\Email::class,
        \OxidEsales\Eshop\Core\Model\ListModel::class
            => \Wirecard\Oxid\Extend\ListModel::class,
    ],
    'controllers' => [
        'wcpg_transaction'
            => \Wirecard\Oxid\Controller\Admin\TransactionController::class,
        'wcpg_transaction_list'
            => \Wirecard\Oxid\Controller\Admin\TransactionList::class,
        'wcpg_transaction_payment_details'
            => \Wirecard\Oxid\Controller\Admin\Transaction\TransactionTabPaymentDetails::class,
        'wcpg_transaction_transaction_details'
            => \Wirecard\Oxid\Controller\Admin\Transaction\TransactionTabTransactionDetails::class,
        'wcpg_transaction_account_holder'
            => \Wirecard\Oxid\Controller\Admin\Transaction\TransactionTabAccountHolder::class,
        'wcpg_transaction_shipping'
            => \Wirecard\Oxid\Controller\Admin\Transaction\TransactionTabShipping::class,
        'wcpg_transaction_post_processing'
            => \Wirecard\Oxid\Controller\Admin\Transaction\TransactionTabPostProcessing::class,
        'wcpg_order_transactions'
            => \Wirecard\Oxid\Controller\Admin\Order\OrderTabTransactions::class,
        'wcpg_order_transaction_details'
            => \Wirecard\Oxid\Controller\Admin\Order\OrderTabTransactionDetails::class,
        'wcpg_order_sepa_mandate'
            => \Wirecard\Oxid\Controller\Admin\Order\OrderTabSepaMandate::class,
        'wcpg_notifyhandler'
            => \Wirecard\Oxid\Controller\NotifyHandler::class,
        'wcpg_form_interaction'
            => \Wirecard\Oxid\Controller\FormInteractionController::class,
        'wcpg_module_support'
            => \Wirecard\Oxid\Controller\Admin\ModuleSupport::class,
        'wcpg_transaction_response_details'
            => \Wirecard\Oxid\Controller\Admin\Transaction\TransactionTabResponseDetails::class,
    ],
    'blocks' => [
        [
            'template' => 'payment_main.tpl',
            'block' => 'admin_payment_main_form',
            'file' => 'views/admin/blocks/wd_admin_payment_main_form.tpl',
        ],
        [
            'template' => 'order_list.tpl',
            'block' => 'admin_order_list_colgroup',
            'file' => 'views/admin/blocks/wd_admin_order_list_colgroup.tpl',
            'position' => 10,
        ],
        [
            'template' => 'order_list.tpl',
            'block' => 'admin_order_list_filter',
            'file' => 'views/admin/blocks/wd_admin_order_list_filter.tpl',
            'position' => 10,
        ],
        [
            'template' => 'order_list.tpl',
            'block' => 'admin_order_list_sorting',
            'file' => 'views/admin/blocks/wd_admin_order_list_sorting.tpl',
            'position' => 10,
        ],
        [
            'template' => 'order_list.tpl',
            'block' => 'admin_order_list_item',
            'file' => 'views/admin/blocks/wd_admin_order_list_item.tpl',
            'position' => 10,
        ],
        [
            'template' => 'page/checkout/order.tpl',
            'block' => 'checkout_order_main',
            'file' => 'views/blocks/profiling_tags.tpl',
        ],
        [
            'template' => 'page/checkout/payment.tpl',
            'block' => 'checkout_payment_errors',
            'file' => 'views/blocks/checkout_errors.tpl',
        ],
        [
            'template' => 'page/checkout/thankyou.tpl',
            'block' => 'checkout_thankyou_info',
            'file' => 'views/blocks/thankyou.tpl',
        ],
        [
            'template' => 'page/checkout/order.tpl',
            'block' => 'shippingAndPayment',
            'file' => 'views/blocks/wirecard_credit_card_fields.tpl',
        ],
        [
            'theme' => 'azure',
            'template' => 'page/account/order.tpl',
            'block' => 'account_order_persparams',
            'file' => 'views/blocks/accountorder_azure.tpl',
        ],
        [
            'template' => 'page/account/order.tpl',
            'block' => 'account_order_history_cart_items',
            'file' => 'views/blocks/accountorder.tpl',
        ],
        [
            'template' => 'email/html/order_cust.tpl',
            'block' => 'email_html_order_cust_orderemail',
            'file' => 'views/blocks/email_html_order_cust_orderemail.tpl',
        ],
        [
            'template' => 'email/plain/order_cust.tpl',
            'block' => 'email_plain_order_cust_orderemail',
            'file' => 'views/blocks/email_plain_order_cust_orderemail.tpl',
        ],
        [
            'template' => 'email/html/order_owner.tpl',
            'block' => 'email_html_order_owner_orderemail',
            'file' => 'views/blocks/email_html_order_owner_orderemail.tpl',
        ],
        [
            'template' => 'email/plain/order_owner.tpl',
            'block' => 'email_plain_order_owner_orderemail',
            'file' => 'views/blocks/email_plain_order_owner_orderemail.tpl',
        ],
        [
            'template' => 'module_config.tpl',
            'block' => 'admin_module_config_form',
            'file' => 'views/admin/blocks/admin_module_config_form.tpl',
        ],
        [
            'template' => 'payment_main.tpl',
            'block' => 'admin_payment_main_editor',
            'file' => 'views/admin/blocks/admin_payment_main_editor.tpl',
        ],
        [
            'template' => 'module_config.tpl',
            'block' => 'admin_module_config_form',
            'file' => 'views/admin/blocks/admin_module_config_form.tpl',
        ],
        [
            'template' => 'page/checkout/payment.tpl',
            'block' => 'select_payment',
            'file' => 'views/blocks/select_payment.tpl',
        ],
        [
            'template' => 'page/checkout/order.tpl',
            'block' => 'checkout_order_btn_confirm_bottom',
            'file' => 'views/blocks/order_button.tpl',
        ],
    ],
    'templates'         => [
        'transaction.tpl'                   => 'wirecard/paymentgateway/views/admin/tpl/transaction.tpl',
        'transaction_list.tpl'              => 'wirecard/paymentgateway/views/admin/tpl/transaction_list.tpl',
        'tab_simple.tpl'                    => 'wirecard/paymentgateway/views/admin/tpl/tab_simple.tpl',
        'tab_table.tpl'                     => 'wirecard/paymentgateway/views/admin/tpl/tab_table.tpl',
        'tab_post_processing.tpl'           => 'wirecard/paymentgateway/views/admin/tpl/tab_post_processing.tpl',
        'tab_sepa_mandate.tpl'              => 'wirecard/paymentgateway/views/admin/tpl/tab_sepa_mandate.tpl',
        'form_interaction.tpl'              => 'wirecard/paymentgateway/views/form_interaction.tpl',
        'module_support.tpl'                => 'wirecard/paymentgateway/views/admin/tpl/module_support.tpl',
        'module_support_email.tpl'          => 'wirecard/paymentgateway/views/admin/tpl/email/module_support_email.tpl',
        'live_chat.tpl'                     => 'wirecard/paymentgateway/views/admin/inc/live_chat.tpl',
        'sepa_mandate_modal.tpl'            => 'wirecard/paymentgateway/views/blocks/sepa_mandate_modal.tpl',
        'sepa_mandate.tpl'                  => 'wirecard/paymentgateway/views/blocks/sepa_mandate.tpl',
    ],
    'events'            => [
        'onActivate'        => '\Wirecard\Oxid\Core\OxidEeEvents::onActivate',
        'onDeactivate'      => '\Wirecard\Oxid\Core\OxidEeEvents::onDeactivate',
    ],
    /**
     * The settings below result in a set of auto-generated translatable keys. For the PhraseApp parsing script to pick
     * them up, we construct the list of keys below as a comment:
     *
     * translate('SHOP_MODULE_wd_email_on_pending_orders')
     * translate('SHOP_MODULE_GROUP_wd_emails')
     */
    'settings' => [
        [
            'group' => 'wd_emails',
            'name' => 'wd_email_on_pending_orders',
            'type' => 'bool',
            'value' => 'false',
        ],
    ],
];
