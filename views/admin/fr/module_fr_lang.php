<?php
/**
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*/

$sLangName = 'Français';

$aLang = array(
    'charset' => 'UTF-8',
    'wd_accept' => 'Accept',
    'wd_account_holder_title' => 'Titulaire du compte',
    'wd_amount' => 'Montant',
    'wd_bic' => 'Code BIC',
    'wd_birthdate_input' => 'Date de naissance',
    'wd_cancel' => 'Annuler',
    'wd_canceled_payment_process' => 'Vous avez annulé le processus de paiement.',
    'wd_capture' => 'Capture',
    'wd_city' => 'Ville',
    'wd_company_name_input' => 'Company',
    'wd_config_additional_info' => 'Envoyer les renseignements complémentaires',
    'wd_config_additional_info_desc' => 'Des renseignements complémentaires doivent être fournis dans le cadre de la prévention des fraudes. Ces renseignements complémentaires incluent l’adresse de facturation/livraison, le panier et le descripteur.',
    'wd_config_allowed_currencies' => 'Devises autorisées',
    'wd_config_allowed_currencies_desc' => 'L’écran Mode de paiement Facture avec garantie de paiement n’est affiché que si la devise activée fait partie des devises sélectionnées ici.',
    'wd_config_allow_changed_shipping' => 'Autoriser la modification de l’adresse de livraison',
    'wd_config_allow_changed_shipping_desc' => 'Si cette option est désactivée, le client doit saisir de nouveau les informations de sa carte de crédit si l’adresse de livraison a été modifiée entre deux commandes.',
    'wd_config_base_url' => 'Adresse du serveur Wirecard',
    'wd_config_base_url_desc' => 'L’adresse du serveur Wirecard (par ex. https://api.wirecard.com).',
    'wd_config_billing_countries' => 'Pays de facturation autorisés',
    'wd_config_billing_countries_desc' => 'L’écran Mode de paiement Facture avec garantie de paiement n’est affiché lors du processus de paiement que si le pays de facturation du client fait partie des pays sélectionnés ici. Appuyez sur Ctrl et cliquez pour sélectionner. Présélection par défaut : Autriche et Allemagne.',
    'wd_config_billing_shipping' => 'Adresse de facturation/livraison identique',
    'wd_config_billing_shipping_desc' => 'Si ce champ est activé, l’écran Mode de paiement Facture avec garantie de paiement n’est affiché que si l’adresse de facturation est identique à l’adresse de livraison.',
    'wd_config_country_code' => 'Country Code',
    'wd_config_country_code_desc' => 'Sofort. requires a valid country code to use the correct logo (i.e. en_gb).',
    'wd_config_creditor_id' => 'Identifiant créancier',
    'wd_config_creditor_id_desc' => 'Avec le mode paiement par prélèvement SEPA, un identifiant créancier est obligatoire pour créer le mandat de prélèvement SEPA. Vous pouvez demander un identifiant créancier auprès de votre institution financière.',
    'wd_config_delete_cancel_order' => 'Supprimer la commande annulée',
    'wd_config_delete_cancel_order_desc' => 'Supprimer automatiquement la commande après l’annulation du processus de paiement.',
    'wd_config_delete_failure_order' => 'Supprimer la commande échouée',
    'wd_config_delete_failure_order_desc' => 'Supprimer automatiquement la commande après l’échec du processus de paiement.',
    'wd_config_descriptor' => 'Descripteur',
    'wd_config_descriptor_desc' => 'Envoyer le texte qui est affiché sur le relevé bancaire délivré à votre client par l’institution financière.',
    'wd_config_email' => 'Votre adresse e-mail',
    'wd_config_enable_bic' => 'Code BIC activé',
    'wd_config_http_password' => 'HTTP Password',
    'wd_config_http_user' => 'HTTP User',
    'wd_config_logo_variant' => 'Version du logo',
    'wd_config_logo_variant_desc' => 'Montrer soit la version du logo standard ou descriptive à vos clients.',
    'wd_config_merchant_account_id' => 'MAID (Identifiant de compte marchand)',
    'wd_config_merchant_account_id_desc' => 'Identifiant unique affecté à votre compte marchand.',
    'wd_config_merchant_secret' => 'Secret Key',
    'wd_config_merchant_secret_desc' => 'Secret Key est obligatoire pour générer la signature digitale des paiements.',
    'wd_config_message' => 'Votre message',
    'wd_config_payment_action' => 'Action de paiement',
    'wd_config_payment_action_desc' => 'Sélectionnez « Acheter » pour enregistrer/facturer automatiquement votre commande ou « Autorisation » pour enregistrer/facturer manuellement.',
    'wd_config_payolution_terms_url' => 'Payolution URL',
    'wd_config_payolution_terms_url_desc' => 'Mandatory if require consent is set to yes',
    'wd_config_reply_to' => 'Reply to (optionally)',
    'wd_config_require_consent' => 'Require consent',
    'wd_config_require_consent_desc' => 'Consumer must agree with the terms before proceeding with the checkout process.',
    'wd_config_shipping_countries' => 'Pays d’expédition autorisés',
    'wd_config_shipping_countries_desc' => 'L’écran Mode de paiement Facture avec garantie de paiement n’est affiché que si le pays d’expédition du client fait partie des pays sélectionnés ici. Appuyez sur Ctrl et cliquez pour sélectionner. Présélection par défaut : Autriche et Allemagne.',
    'wd_config_shopping_basket' => 'Panier de commande',
    'wd_config_shopping_basket_desc' => 'Pour la confirmation de la commande, le paiement supporte le panier de commande affiché lors du processus de paiement. Pour activer cette fonction, cochez le champ Panier de commande.',
    'wd_config_ssl_max_limit' => 'Valeur limite max. sans le protocole 3-D Secure',
    'wd_config_ssl_max_limit_desc' => 'This amount forces 3-D Secure transactions. Enter "null" to disable the Non 3-D Secure Max. Limit.',
    'wd_config_three_d_merchant_account_id' => 'MAID 3-D Secure',
    'wd_config_three_d_merchant_account_id_desc' => 'Identifiant unique affecté à votre compte marchand 3-D Secure. Peut être « null » pour forcer le processus SSL.',
    'wd_config_three_d_merchant_secret' => 'Secret Key 3-D Secure',
    'wd_config_three_d_merchant_secret_desc' => 'Secret Key est obligatoire pour générer la signature digitale du paiement 3-D Secure. Peut être « null » pour forcer le processus SSL.',
    'wd_config_three_d_min_limit' => 'Valeur limite min. avec protocole 3-D Secure',
    'wd_config_three_d_min_limit_desc' => 'This amount forces 3-D Secure transactions. Enter "null" to disable the 3-D Secure Min. Limit.',
    'wd_config_vault' => 'Paiement en un clic',
    'wd_config_vault_desc' => 'Les données de la carte de crédit sont enregistrées pour une utilisation ultérieure.',
    'wd_config_wpp_url' => 'Wirecard Payment Page v2 Adresse (URL WPP v2)',
    'wd_config_wpp_url_desc' => 'Wirecard Payment Page v2 Adresse (URL WPP v2) (ex. https://wpp.wirecard.com).',
    'wd_copy_xml_text' => 'Copier XML',
    'wd_country' => 'Pays',
    'wd_credit' => 'Remboursement',
    'wd_creditor' => 'Créancier',
    'wd_creditor_mandate_id' => 'Identifiant du mandat',
    'wd_currency_config' => 'Each currency has to be configured.',
    'wd_customerId' => 'Identifiant client',
    'wd_date-of-birth' => 'Date de naissance',
    'wd_date_format_php_code' => 'm/d/Y',
    'wd_date_format_user_hint' => 'MM/DD/YYYY',
    'wd_debtor' => 'Débiteur',
    'wd_debtor_acc_owner' => 'Titulaire du compte',
    'wd_default_currency' => 'Devise par défaut',
    'wd_descriptor' => 'Descripteur',
    'wd_email' => 'E-mail',
    'wd_enter_country_code_error' => 'Please enter a valid country code.',
    'wd_enter_valid_email_error' => 'Veuillez saisir une adresse e-mail valide.',
    'wd_error_credentials' => 'Le test a échoué, vérifiez vos informations d’identification.',
    'wd_error_save_failed' => 'Configuration not valid. Save aborted.',
    'wd_first-name' => 'Prénom',
    'wd_gender' => 'Sexe',
    'wd_heading_title' => 'Wirecard',
    'wd_heading_title_alipay_crossborder' => 'Wirecard Alipay Cross-border',
    'wd_heading_title_creditcard' => 'Wirecard Carte de crédit',
    'wd_heading_title_eps' => 'Wirecard Virement eps',
    'wd_heading_title_giropay' => 'Wirecard giropay',
    'wd_heading_title_ideal' => 'Wirecard iDEAL',
    'wd_heading_title_payolution_b2b' => 'Wirecard Guaranteed Invoice (Payolution B2B)',
    'wd_heading_title_payolution_b2b_custom' => 'Wirecard Invoice (Payolution B2B)',
    'wd_heading_title_payolution_invoice' => 'Wirecard Guaranteed Invoice (Payolution B2C)',
    'wd_heading_title_payolution_invoice_custom' => 'Wirecard Invoice (Payolution B2C)',
    'wd_heading_title_paypal' => 'Wirecard PayPal',
    'wd_heading_title_pia' => 'Wirecard Paiement à l’avance',
    'wd_heading_title_poi' => 'Wirecard Paiement sur facture',
    'wd_heading_title_ratepayinvoice' => 'Wirecard Facture avec garantie de paiement par Wirecard',
    'wd_heading_title_ratepayinvoice_custom' => 'Wirecard Invoice by Wirecard',
    'wd_heading_title_sepact' => 'Wirecard Virement SEPA',
    'wd_heading_title_sepadd' => 'Wirecard Prélèvement SEPA',
    'wd_heading_title_sofortbanking' => 'Wirecard Sofort.',
    'wd_heading_title_support' => 'Support Wirecard',
    'wd_heading_title_transaction_details' => 'Transactions Wirecard',
    'wd_house-extension' => 'Extension maison',
    'wd_iban' => 'Code IBAN',
    'wd_ideal_legend' => 'Sélectionner votre banque',
    'wd_ip' => 'Adresse IP',
    'wd_last-name' => 'Nom de famille',
    'wd_maid' => 'MAID (Identifiant de compte marchand)',
    'wd_manipulated' => 'manipulated',
    'wd_merchant-crm-id' => 'Identifiant CRM du marchand',
    'wd_message_empty_error' => 'Message cannot be empty.',
    'wd_more_info' => 'More info',
    'wd_no' => 'No',
    'wd_orderNumber' => 'Numéro de commande',
    'wd_order_error' => 'Une erreur est survenue durant le processus de paiement. Veuillez réessayer.',
    'wd_order_error_info' => 'An error occurred in the payment process. The order has been canceled.',
    'wd_order_status' => 'Order status',
    'wd_order_status_authorized' => 'Autorisé',
    'wd_order_status_cancelled' => 'Cancelled',
    'wd_order_status_failed' => 'Failed',
    'wd_order_status_pending' => 'Pending',
    'wd_order_status_purchased' => 'Paid',
    'wd_order_status_refunded' => 'Refunded',
    'wd_panel_action' => 'Action',
    'wd_panel_amount' => 'Montant',
    'wd_panel_currency' => 'Devise',
    'wd_panel_details' => 'Détails',
    'wd_panel_order_id' => 'Order Reference',
    'wd_panel_order_number' => 'Numéro de commande',
    'wd_panel_parent_transaction_id' => 'Identifiant de la transaction parent',
    'wd_panel_payment_method' => 'Mode de paiement',
    'wd_panel_provider_transaction_id' => 'Provider Transaction ID',
    'wd_panel_transaction' => 'Transaction',
    'wd_panel_transaction_copy' => 'Copier XML',
    'wd_panel_transaction_date' => 'Date',
    'wd_panel_transaction_state' => 'État de transaction',
    'wd_panel_transcation_id' => 'Identifiant de la transaction',
    'wd_paymentMethod' => 'Mode de paiement',
    'wd_payment_awaiting' => 'En attente du paiement à partir de Wirecard.',
    'wd_payment_cancelled_text' => 'Payment was cancelled.',
    'wd_payment_cost' => 'Payment cost',
    'wd_payment_failed_text' => 'Payment process failed.',
    'wd_payment_method_settings' => 'Paramètres du mode de paiement',
    'wd_payment_refunded_text' => 'Payment was refunded.',
    'wd_payment_success_text' => 'Payment process successful.',
    'wd_payolution_terms' => 'I agree that the data which are necessary for the liquidation of purchase on account and which are used to complete the identity and credit check are transmitted to Payolution. My <u><a href="%s" target="_blank">consent</a></u> can be revoked at any time with effect for the future.',
    'wd_phone' => 'Téléphone',
    'wd_pia_ptrid' => 'Provider Transaction Reference ID',
    'wd_postal-code' => 'Code postal',
    'wd_ptrid' => 'Identifiant de référence du fournisseur de transactions',
    'wd_ratepayinvoice_fields_error' => 'Âge minimum requis pour le mode de paiement Facture avec garantie de paiement : 18.',
    'wd_redirect_text' => 'Vous êtes redirigé. Veuillez patienter.',
    'wd_refund' => 'Remboursement',
    'wd_requestedAmount' => 'Montant',
    'wd_requestId' => 'Identifiant de la demande',
    'wd_save_to_user_account' => 'Save data to your user account',
    'wd_secured' => 'secured',
    'wd_send_email' => 'Soumettre',
    'wd_sepa_mandate' => 'Mandat SEPA',
    'wd_sepa_text_1' => 'J’autorise le créancier',
    'wd_sepa_text_2' => 'à envoyer des ordres à ma banque pour initier un et un seul débit de mon compte. En même temps, j’instruis ma banque de débiter mon compte conformément aux instructions du créancier.',
    'wd_sepa_text_2b' => '.',
    'wd_sepa_text_3' => 'Remarque : Dans le cadre de mes droits, j’ai droit à un remboursement selon les conditions générales du contrat avec ma banque. Toute demande de remboursement doit être présentée dans les 8 semaines suivant la date de débit de votre compte.',
    'wd_sepa_text_4' => 'J’accepte irrévocablement que si le prélèvement n’est pas honoré ou s’il y a une opposition au prélèvement bancaire, ma banque en informera le créancier',
    'wd_sepa_text_5' => 'mon nom, mon prénom, mon adresse et ma date de naissance.',
    'wd_sepa_text_6' => 'J’ai lu et accepté les informations du mandat de prélèvement SEPA.',
    'wd_shipping-method' => 'Mode de livraison',
    'wd_shipping_title' => 'Livraison',
    'SHOP_MODULE_GROUP_wd_emails' => 'Emails',
    'SHOP_MODULE_wd_email_on_pending_orders' => 'Send notification emails when order pending',
    'wd_social-security-number' => 'Numéro de sécurité sociale',
    'wd_state_awaiting' => 'awaiting',
    'wd_state_closed' => 'closed',
    'wd_state_error' => 'error',
    'wd_state_success' => 'success',
    'wd_street1' => 'Rue',
    'wd_street2' => 'Rue 2',
    'wd_success_credentials' => 'Le test de la configuration du marchand a été effectué avec succès.',
    'wd_success_email' => 'E-mail envoyé avec succès.',
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
    'wd_test_credentials' => 'Tester les informations d’identification',
    'wd_text_article_name' => 'Nom du produit',
    'wd_text_article_number' => 'Numéro d’article',
    'wd_text_backend_operations' => 'Traitements ultérieurs éventuels',
    'wd_text_delete' => 'Supprimer',
    'wd_text_generic_error' => 'Action could not be performed.',
    'wd_text_generic_success' => 'Action performed successfully.',
    'wd_text_list' => 'Transactions',
    'wd_text_logo_variant_descriptive' => 'Descriptif',
    'wd_text_logo_variant_standard' => 'Standard',
    'wd_text_message' => 'Message',
    'wd_text_no_data_available' => 'No data available.',
    'wd_text_no_further_operations_possible' => 'No further operations possible.',
    'wd_text_order_no_transactions' => 'There are no associated transactions for this order.',
    'wd_text_payment_action_pay' => 'Achat',
    'wd_text_payment_action_reserve' => 'Autorisation',
    'wd_text_quantity' => 'Quantité',
    'wd_text_support' => 'Support',
    'wd_text_vault' => 'Paiement en un clic',
    'wd_three_d_link_text' => 'Non 3-D Secure and 3-D Secure Limits',
    'wd_timeStamp' => 'Date',
    'wd_total_amount_not_in_range_text' => 'Total amount not in allowed range.',
    'wd_transactionID' => 'Identifiant de la transaction',
    'wd_transactionState' => 'État de transaction',
    'wd_transactionType' => 'Type de transaction',
    'wd_transaction_details_title' => 'Détails de la transaction',
    'wd_transaction_response_details' => 'Response Details',
    'wd_transfer_notice' => 'Veuillez transférer le montant en utilisant les données suivantes :',
    'wd_unmatched' => 'unmatched',
    'wd_vault_changed_shipping_text' => 'Votre adresse de livraison a changé depuis votre dernière commande. Pour des raisons de sécurité, vous devez saisir vos nouvelles données de carte de crédit.',
    'wd_vault_save_text' => 'Enregistrer pour une utilisation ultérieure.',
    'wd_vault_use_new_text' => 'Utiliser une nouvelle carte de crédit',
    'wd_wait_for_final_status' => 'Please, wait for additional email with the final status of your payment.',
    'wd_yes' => 'Yes',
);
