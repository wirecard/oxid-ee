<?php
/**
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*/

$sLangName = '日本語';

$aLang = array(
    'charset' => 'UTF-8',
    'wd_accept' => 'Accept',
    'wd_account_holder_title' => '口座名義人',
    'wd_amount' => 'Amount',
    'wd_bic' => 'BIC:',
    'wd_birthdate_input' => '誕生日',
    'wd_cancel' => 'Cancel',
    'wd_canceled_payment_process' => '支払い処理が取り消されました。',
    'wd_capture' => 'Capture',
    'wd_city' => '市町村',
    'wd_company_name_input' => 'Company',
    'wd_config_additional_info' => '追加情報を送信する',
    'wd_config_additional_info_desc' => '詐欺防止のための追加データが送信されます。この追加データには、請求先 / 配送先の住所、買い物かご、および記述子が含まれます。',
    'wd_config_allowed_currencies' => '許可されている通貨',
    'wd_config_allowed_currencies_desc' => '有効な通貨がこれらの選択済み通貨のいずれかである場合、保証付きインボイスの支払い方法だけが表示されます。',
    'wd_config_allow_changed_shipping' => '配送先住所の変更を許可する',
    'wd_config_allow_changed_shipping_desc' => '無効の場合、購入者は2回の注文の間に配送先住所が変わったら、クレジットカードの詳細を再入力する必要があります。',
    'wd_config_base_url' => 'ベース URL',
    'wd_config_base_url_desc' => 'Wirecard ベース URL。(例: https://api.wirecard.com)',
    'wd_config_billing_countries' => '許可されている請求先国',
    'wd_config_billing_countries_desc' => '消費者の発送先国がこれらの選択された国々のいずれかと一致する場合、保証付きインボイスの支払い方法だけが表示されます。\n\t\t\t\tあらかじめ許可されている国々は次の通りです: AT、DE。',
    'wd_config_billing_shipping' => '請求先/発送先の住所は、同一でなければなりません',
    'wd_config_billing_shipping_desc' => '有効にすると、請求先/発送先の住所が同一の場合、保証付きインボイスの支払い方法だけが表示されます',
    'wd_config_country_code' => 'Country Code',
    'wd_config_country_code_desc' => 'Sofort. requires a valid country code to use the correct logo (i.e. en_gb).',
    'wd_config_creditor_id' => '債権者 ID',
    'wd_config_creditor_id_desc' => 'SEPA では、SEPA ダイレクト デビット委託の作成に債権者 ID が必要です。債権者 ID は、信頼のおける金融機関でお申し込みください。',
    'wd_config_delete_cancel_order' => 'キャンセルされた注文を削除する',
    'wd_config_delete_cancel_order_desc' => '支払処理がキャンセルされると自動的に注文を削除します。',
    'wd_config_delete_failure_order' => '失敗した注文を削除する',
    'wd_config_delete_failure_order_desc' => '支払処理に失敗すると自動的に注文を削除します。',
    'wd_config_descriptor' => '記述子',
    'wd_config_descriptor_desc' => '金融サービス プロバイダーが消費者に発行する取引明細書に表示されるテキストを送信します',
    'wd_config_email' => 'お客様のメール アドレス',
    'wd_config_enable_bic' => 'BIC が有効',
    'wd_config_http_password' => 'HTTP パスワード',
    'wd_config_http_user' => 'HTTP ユーザー',
    'wd_config_logo_variant' => 'ロゴバージョン',
    'wd_config_logo_variant_desc' => '購入者に標準的または記述的ロゴバージョンのいずれかを表示します。',
    'wd_config_merchant_account_id' => '販売者アカウント ID',
    'wd_config_merchant_account_id_desc' => '販売者アカウントに割り当てられる一意の ID。',
    'wd_config_merchant_secret' => '秘密鍵',
    'wd_config_merchant_secret_desc' => '秘密鍵は、支払いのデジタル署名の計算に必須です。',
    'wd_config_message' => 'メッセージ',
    'wd_config_payment_action' => '支払い処理',
    'wd_config_payment_action_desc' => '注文のキャプチャ / インボイスを自動的に行う場合は「キャプチャ」を、キャプチャ / インボイスを手動で行う場合は「承認」を選択します。',
    'wd_config_payolution_terms_url' => 'Payolution URL',
    'wd_config_payolution_terms_url_desc' => 'Mandatory if require consent is set to yes',
    'wd_config_reply_to' => 'Reply to (optionally)',
    'wd_config_require_consent' => 'Require consent',
    'wd_config_require_consent_desc' => 'Consumer must agree with the terms before proceeding with the checkout process.',
    'wd_config_shipping_countries' => '許可されている発送先国',
    'wd_config_shipping_countries_desc' => '消費者の請求先国がこれらの選択された国々のいずれかと一致する場合、保証付きインボイスの支払い方法だけが表示されます。Wirecard 契約により許可されている国々は次の通りです: AT、DE。',
    'wd_config_shopping_basket' => 'ショッピング バスケット',
    'wd_config_shopping_basket_desc' => '確認のため、この支払方法では注文中に買い物かごが表示されます。この機能を有効化するには、買い物かごを有効にします。',
    'wd_config_ssl_max_limit' => '3-D セキュアの最大限度ではない',
    'wd_config_ssl_max_limit_desc' => 'This amount forces 3-D Secure transactions. Enter "null" to disable the Non 3-D Secure Max. Limit.',
    'wd_config_three_d_merchant_account_id' => '3-D セキュア販売者アカウント ID',
    'wd_config_three_d_merchant_account_id_desc' => '3D セキュア販売者アカウントに割り当てられた一意の識別子。',
    'wd_config_three_d_merchant_secret' => '3-D セキュア秘密鍵',
    'wd_config_three_d_merchant_secret_desc' => '秘密鍵は、3D セキュア支払いのデジタル署名の計算に必須です。',
    'wd_config_three_d_min_limit' => '3-D セキュアの最小限度',
    'wd_config_three_d_min_limit_desc' => 'This amount forces 3-D Secure transactions. Enter "null" to disable the 3-D Secure Min. Limit.',
    'wd_config_vault' => 'ワンクリック注文',
    'wd_config_vault_desc' => 'クレジット カードを保存しておけば、次回からはクレジット カードの詳細を入力しなくても使用できるようになります。',
    'wd_config_wpp_url' => 'Wirecard Payment Page v2 Address (URL WPP v2)',
    'wd_config_wpp_url_desc' => 'Wirecard Payment Page v2 Address (URL WPP v2) (e.g. https://wpp.wirecard.com).',
    'wd_copy_xml_text' => 'XMLをコピーする',
    'wd_country' => '国',
    'wd_credit' => '返金する',
    'wd_creditor' => '債権者',
    'wd_creditor_mandate_id' => 'マンデートID',
    'wd_currency_config' => 'Each currency has to be configured.',
    'wd_customerId' => 'カスタマーID',
    'wd_date-of-birth' => '送料',
    'wd_date_format_php_code' => 'm/d/Y',
    'wd_date_format_user_hint' => 'MM/DD/YYYY',
    'wd_debtor' => '債務者',
    'wd_debtor_acc_owner' => '口座名義人',
    'wd_default_currency' => 'デフォルト通貨',
    'wd_descriptor' => '記述子',
    'wd_email' => '電子メール',
    'wd_enter_country_code_error' => 'Please enter a valid country code.',
    'wd_enter_valid_email_error' => '有効なメールアドレスを入力してください。',
    'wd_error_credentials' => 'テストが失敗しました。信用証明を確認してください。',
    'wd_error_save_failed' => 'Configuration not valid. Save aborted.',
    'wd_first-name' => '名',
    'wd_gender' => '性別',
    'wd_heading_title' => 'Wirecard',
    'wd_heading_title_alipay_crossborder' => 'Wirecard Alipay Cross-border',
    'wd_heading_title_creditcard' => 'Wirecard クレジット カード',
    'wd_heading_title_eps' => 'Wirecard eps-Überweisung',
    'wd_heading_title_giropay' => 'Wirecard Giropay',
    'wd_heading_title_ideal' => 'Wirecard iDEAL',
    'wd_heading_title_payolution_b2b' => 'Wirecard Guaranteed Invoice (Payolution B2B)',
    'wd_heading_title_payolution_b2b_custom' => 'Wirecard Invoice (Payolution B2B)',
    'wd_heading_title_payolution_invoice' => 'Wirecard Guaranteed Invoice (Payolution B2C)',
    'wd_heading_title_payolution_invoice_custom' => 'Wirecard Invoice (Payolution B2C)',
    'wd_heading_title_paypal' => 'Wirecard PayPal',
    'wd_heading_title_pia' => 'Wirecardの前払い',
    'wd_heading_title_poi' => 'Wirecardの請求書払い',
    'wd_heading_title_ratepayinvoice' => 'Wirecard 保証付きインボイス',
    'wd_heading_title_ratepayinvoice_custom' => 'Wirecard Invoice by Wirecard',
    'wd_heading_title_sepact' => 'Wirecard SEPA Credit Transfer',
    'wd_heading_title_sepadd' => 'Wirecard SEPA Direct Debit',
    'wd_heading_title_sofortbanking' => 'Wirecard Sofort。',
    'wd_heading_title_support' => 'サポート',
    'wd_heading_title_transaction_details' => 'Transaksi Wirecard',
    'wd_house-extension' => '内線',
    'wd_iban' => 'IBAN:',
    'wd_ideal_legend' => '銀行を選択する',
    'wd_ip' => 'IPアドレス',
    'wd_last-name' => '氏',
    'wd_maid' => 'MAID',
    'wd_manipulated' => 'manipulated',
    'wd_merchant-crm-id' => 'マーチャントCRM ID',
    'wd_message_empty_error' => 'Message cannot be empty.',
    'wd_more_info' => 'More info',
    'wd_no' => 'No',
    'wd_orderNumber' => '注文番号',
    'wd_order_error' => '支払い処理中にエラーが発生しました。もう一度実行してください。',
    'wd_order_error_info' => 'An error occurred in the payment process. The order has been canceled.',
    'wd_order_status' => 'Order status',
    'wd_order_status_authorized' => '承認済み',
    'wd_order_status_cancelled' => 'Cancelled',
    'wd_order_status_failed' => 'Failed',
    'wd_order_status_pending' => 'Pending',
    'wd_order_status_purchased' => 'Paid',
    'wd_order_status_refunded' => 'Refunded',
    'wd_panel_action' => 'アクション',
    'wd_panel_amount' => '金額',
    'wd_panel_currency' => '通貨',
    'wd_panel_details' => '明細',
    'wd_panel_order_id' => 'Order Reference',
    'wd_panel_order_number' => 'Order Number',
    'wd_panel_parent_transaction_id' => '親トランザクション ID',
    'wd_panel_payment_method' => '支払い方法',
    'wd_panel_provider_transaction_id' => 'Provider Transaction ID',
    'wd_panel_transaction' => 'トランザクション',
    'wd_panel_transaction_copy' => 'XMLをコピーする',
    'wd_panel_transaction_date' => '日付',
    'wd_panel_transaction_state' => 'トランザクションの状態',
    'wd_panel_transcation_id' => 'トランザクション ID',
    'wd_paymentMethod' => '支払方法',
    'wd_payment_awaiting' => 'Wirecard からの支払いを待機しています',
    'wd_payment_cancelled_text' => 'Payment was cancelled.',
    'wd_payment_cost' => 'Payment cost',
    'wd_payment_failed_text' => 'Payment process failed.',
    'wd_payment_method_settings' => 'Payment method settings',
    'wd_payment_refunded_text' => 'Payment was refunded.',
    'wd_payment_success_text' => 'Payment process successful.',
    'wd_payolution_terms' => 'I agree that the data which are necessary for the liquidation of purchase on account and which are used to complete the identity and credit check are transmitted to Payolution. My <u><a href="%s" target="_blank">consent</a></u> can be revoked at any time with effect for the future.',
    'wd_phone' => '電話',
    'wd_pia_ptrid' => 'Provider Transaction Reference ID',
    'wd_postal-code' => '郵便番号',
    'wd_ptrid' => 'プロバイダーのトランザクション参照 ID',
    'wd_ratepayinvoice_fields_error' => '注文を行うには、18 歳以上でなければなりません。',
    'wd_redirect_text' => 'リダイレクトしています。お待ちください',
    'wd_refund' => '返金する',
    'wd_requestedAmount' => '金額',
    'wd_requestId' => 'リクエストID',
    'wd_save_to_user_account' => 'Save data to your user account',
    'wd_secured' => 'secured',
    'wd_send_email' => '送信する',
    'wd_sepa_mandate' => 'SEPAマンデート',
    'wd_sepa_text_1' => '債権者が',
    'wd_sepa_text_2' => '私の口座から1回のダイレクトデビットを回収するように、私の銀行に指示を送信することを許可します。同時に、私は、債権者からの指示に従い、私の銀行が口座から引き落とすように銀行に指示します。',
    'wd_sepa_text_2b' => '.',
    'wd_sepa_text_3' => '注記：私の権利の一部として、私は、銀行との合意の条項および条件に基づき、返金を受け取る資格があります。返金は、私の口座から引き落としがあった日から起算して8週間以内に請求する必要があります。',
    'wd_sepa_text_4' => '私は、取消不能の形式で、ダイレクトデビットが無効になった場合や、ダイレクトデビットに対する異議が存在する場合には、私の銀行が債権者',
    'wd_sepa_text_5' => 'に対して私の氏名、住所、生年月日を開示することに同意します。',
    'wd_sepa_text_6' => '私は、SEPAダイレクトデビットマンデート情報を読んで同意しました。',
    'wd_shipping-method' => '配送方法',
    'wd_shipping_title' => '送料',
    'SHOP_MODULE_GROUP_wd_emails' => 'Emails',
    'SHOP_MODULE_wd_email_on_pending_orders' => 'Send notification emails when order pending',
    'wd_social-security-number' => '社会保障番号',
    'wd_state_awaiting' => 'awaiting',
    'wd_state_closed' => 'closed',
    'wd_state_error' => 'error',
    'wd_state_success' => 'success',
    'wd_street1' => '番地',
    'wd_street2' => '番地2',
    'wd_success_credentials' => '販売者構成のテストが成功しました。',
    'wd_success_email' => 'メールの送信に成功しました',
    'wd_support_description' => 'System information will be automatically added to your message and will be sent to',
    'wd_support_email_from' => 'From',
    'wd_support_email_modules' => 'Other modules',
    'wd_support_email_module_id' => 'Module ID',
    'wd_support_email_module_title' => 'Module Title',
    'wd_support_email_module_version' => 'Module Version',
    'wd_support_email_php' => 'PHP Version',
    'wd_support_email_reply_to' => 'Reply to',
    'wd_support_email_shop_edition' => 'OXID eShop Edition',
    'wd_support_email_shop_version' => 'OXID eShop Version',
    'wd_support_email_subject' => 'OXID eShop support request',
    'wd_support_email_system' => 'Server Info',
    'wd_support_send_error' => 'Support e-mail could not be sent.',
    'wd_test_credentials' => 'テスト',
    'wd_text_article_name' => '商品名',
    'wd_text_article_number' => '商品番号',
    'wd_text_backend_operations' => '可能な処理後の操作',
    'wd_text_delete' => '削除',
    'wd_text_generic_error' => 'Action could not be performed.',
    'wd_text_generic_success' => 'Action performed successfully.',
    'wd_text_list' => '取引',
    'wd_text_logo_variant_descriptive' => '記述的',
    'wd_text_logo_variant_standard' => '標準的',
    'wd_text_message' => 'メッセージ',
    'wd_text_no_data_available' => 'No data available.',
    'wd_text_no_further_operations_possible' => 'No further operations possible.',
    'wd_text_order_no_transactions' => 'There are no associated transactions for this order.',
    'wd_text_payment_action_pay' => 'Purchase',
    'wd_text_payment_action_reserve' => 'Authorization',
    'wd_text_quantity' => '数',
    'wd_text_support' => 'Support',
    'wd_text_vault' => 'ワンクリック注文',
    'wd_three_d_link_text' => 'Non 3-D Secure and 3-D Secure Limits',
    'wd_timeStamp' => '日付',
    'wd_total_amount_not_in_range_text' => 'Total amount not in allowed range.',
    'wd_transactionID' => '取引ID',
    'wd_transactionState' => '取引の状態',
    'wd_transactionType' => '取引の種類',
    'wd_transaction_details_title' => '取引明細',
    'wd_transaction_response_details' => 'Response Details',
    'wd_transfer_notice' => '以下のデータを使用して金額を送金してください:',
    'wd_unmatched' => 'unmatched',
    'wd_vault_changed_shipping_text' => '前回の注文から配送先住所が変更されています。セキュリティの目的で新規クレジットカードの詳細を入力する必要があります。',
    'wd_vault_save_text' => '後で使用するために保存する',
    'wd_vault_use_new_text' => '新しいクレジット カードを使用する',
    'wd_wait_for_final_status' => 'Please, wait for additional email with the final status of your payment.',
    'wd_yes' => 'Yes',
);
