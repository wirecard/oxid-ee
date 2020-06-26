<?php
/**
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*/

$sLangName = 'Deutsch';

$aLang = array(
    'charset' => 'UTF-8',
    'wd_accept' => 'Akzeptieren',
    'wd_account_holder_title' => 'Kontoinhaber',
    'wd_amount' => 'Betrag',
    'wd_bic' => 'BIC',
    'wd_birthdate_input' => 'Geburtsdatum',
    'wd_cancel' => 'Abbrechen',
    'wd_canceled_payment_process' => 'Sie haben den Bezahlprozess abgebrochen.',
    'wd_capture' => 'Buchen',
    'wd_city' => 'Stadt',
    'wd_company_name_input' => 'Firma',
    'wd_config_additional_info' => 'Zusätzliche Informationen mitsenden',
    'wd_config_additional_info_desc' => 'Zum Schutz vor Betrug werden zusätzliche Daten mitgesendet. Zu diesen zusätzlichen Daten gehören Rechnungs-/Lieferadresse, Warenkorb und Deskriptor.',
    'wd_config_allowed_currencies' => 'Erlaubte Währungen',
    'wd_config_allowed_currencies_desc' => 'Garantierter Kauf auf Rechnung wird nur dann angezeigt, wenn die aktive Währung einer der hier gewählten Währungen entspricht.',
    'wd_config_allow_changed_shipping' => 'Änderung der Lieferadresse zulassen',
    'wd_config_allow_changed_shipping_desc' => 'Ist diese Funktion deaktiviert, muss der Konsument bei Änderung seiner Lieferadresse seine Kartendaten erneut eingeben.',
    'wd_config_base_url' => 'Wirecard Server Adresse (URL)',
    'wd_config_base_url_desc' => 'Wirecard Server Adresse (URL) (z. B. https://api.wirecard.com).',
    'wd_config_billing_countries' => 'Erlaubte Rechnungsländer',
    'wd_config_billing_countries_desc' => 'Garantierter Kauf auf Rechnung wird nur dann angezeigt, wenn das Rechnungsland einem der hier gewählten Länder entspricht. Mehrfachauswahl mit STRG + Klick. Standardvorauswahl: Österreich und Deutschland.',
    'wd_config_billing_shipping' => 'Identische Rechnungs- und Lieferadresse',
    'wd_config_billing_shipping_desc' => 'Bei Aktivierung wird Garantierter Kauf auf Rechnung nur dann beim Bezahlprozess angezeigt, wenn Rechnungs- und Lieferadresse übereinstimmen.',
    'wd_config_challenge_challenge_threed' => 'Sicherheitsabfrage anfordern',
    'wd_config_challenge_indicator' => 'Sicherheitsabfrage',
    'wd_config_challenge_no_challenge' => 'Keine Sicherheitsabfrage anfordern',
    'wd_config_challenge_no_preference' => 'Keine Präferenz',
    'wd_config_country_code' => 'Ländercode',
    'wd_config_country_code_desc' => 'Sofort. benötigt einen Ländercode um das richtige Logo zu verwenden (z.B. de_de).',
    'wd_config_creditor_id' => 'Creditor ID',
    'wd_config_creditor_id_desc' => 'Die Creditor-ID wird benötigt, um das SEPA-Lastschriftmandat zu erstellen. Die Creditor-ID muss beim zuständigen Bankinstitut angefordert werden.',
    'wd_config_delete_cancel_order' => 'Abgebrochene Bestellung löschen',
    'wd_config_delete_cancel_order_desc' => 'Bestellung nach Abbruch des Bezahlprozesses automatisch löschen.',
    'wd_config_delete_failure_order' => 'Fehlgeschlagene Bestellung löschen',
    'wd_config_delete_failure_order_desc' => 'Bestellung nach fehlgeschlagenem Bezahlprozess automatisch löschen.',
    'wd_config_descriptor' => 'Deskriptor',
    'wd_config_descriptor_desc' => 'Aktivieren Sie den Deskriptor, um bei jeder Transaktion eine Referenz zur jeweiligen Bestellung mitzuschicken. Diese Referenz wird im Buchungstext angezeigt, der dem Kosumenten vom Finanzdienstleister übermittelt wird.',
    'wd_config_email' => 'Ihre E-Mail-Adresse',
    'wd_config_enable_bic' => 'BIC aktivieren',
    'wd_config_http_password' => 'Passwort (Password)',
    'wd_config_http_user' => 'Benutzername (Username)',
    'wd_config_logo_variant' => 'Logovariante',
    'wd_config_logo_variant_desc' => 'Konsumenten das Standard-Logo oder die deskriptive Variante zeigen.',
    'wd_config_merchant_account_id' => 'Merchant Account ID (MAID)',
    'wd_config_merchant_account_id_desc' => 'Eindeutiger Identifikator, der Ihrem Händlerkonto zugewiesen ist.',
    'wd_config_merchant_secret' => 'Geheimschlüssel (Secret Key)',
    'wd_config_merchant_secret_desc' => 'Der Geheimschlüssel (Secret Key) wird benötigt, um die Digitale Signatur für Zahlungen zu berechnen.',
    'wd_config_message' => 'Ihre Nachricht',
    'wd_config_payment_action' => 'Zahlungsaktion',
    'wd_config_payment_action_desc' => 'Wählen Sie "Buchung", um automatisch eine Buchung durchzuführen, oder "Autorisierung", um eine manuelle Buchung zu ermöglichen.',
    'wd_config_payolution_terms_url' => 'Payolution-URL',
    'wd_config_payolution_terms_url_desc' => 'Pflichtfeld, wenn "Zustimmung einholen" aktiviert ist.',
    'wd_config_PSD2_information' => 'PSD 2',
    'wd_config_PSD2_information_desc_oxid' => '</a>Angesichts der Bestimmungen der PSD 2 sollten Sie beim <br><u><a target="_blank" href=\'https://github.com/wirecard/oxid-ee/wiki/Credit-Card\'>Checkout</a></u> bestimmte persönliche Daten von Ihren Kunden anfordern,<br> um das Risiko, dass Transaktionen abgelehnt werden, zu reduzieren.',
    'wd_config_reply_to' => 'Antwort an (optional)',
    'wd_config_require_consent' => 'Zustimmung einholen',
    'wd_config_require_consent_desc' => 'Der Konsument muss den Bedingungen zustimmen, um mit dem Checkout fortfahren zu können.',
    'wd_config_shipping_countries' => 'Erlaubte Lieferländer',
    'wd_config_shipping_countries_desc' => 'Garantierter Kauf auf Rechnung wird nur dann angezeigt, wenn das Lieferland einem der hier gewählten Länder entspricht. Mehrfachauswahl mit STRG + Klick. Standardvorauswahl: Österreich und Deutschland.',
    'wd_config_shopping_basket' => 'Warenkorb',
    'wd_config_shopping_basket_desc' => 'Das Zahlungsmittel unterstützt die Anzeige des Warenkorbs während des Checkouts. Um dieses Feature zu verwenden, aktivieren Sie den Warenkorb.',
    'wd_config_ssl_max_limit' => 'Höchstbetrag ohne 3D Secure',
    'wd_config_ssl_max_limit_desc' => 'Dieser Betrag erzwingt 3D Secure Transaktionen. Geben Sie "Null" ein, um den Höchstbetrag für Transaktionen ohne 3D Secure zu deaktivieren.',
    'wd_config_three_d_merchant_account_id' => '3D Secure MAID',
    'wd_config_three_d_merchant_account_id_desc' => 'Eindeutiger Identifikator, der Ihrem 3D-Secure-Händlerkonto zugewiesen ist. Kann auf "Null" gesetzt werden, um SSL-Verschlüsselung zu erzwingen.',
    'wd_config_three_d_merchant_secret' => '3D Secure Secret Key',
    'wd_config_three_d_merchant_secret_desc' => 'Der Geheimschlüssel (Secret Key) wird benötigt, um die Digitale Signatur für die 3D Secure Zahlung zu berechnen. Kann auf "Null" gesetzt werden, um SSL-Verschlüsselung zu erzwingen.',
    'wd_config_three_d_min_limit' => '3D Secure Mindestbetrag',
    'wd_config_three_d_min_limit_desc' => 'Dieser Betrag erzwingt 3D Secure Transaktionen. Geben Sie "Null" ein, um den 3D Secure Mindestbetrag zu deaktivieren.',
    'wd_config_vault' => 'One-Click-Checkout',
    'wd_config_vault_desc' => 'Kartendaten werden für die spätere Verwendung gespeichert.',
    'wd_config_wpp_url' => 'Wirecard Payment Page v2 Adresse (URL WPP v2)',
    'wd_config_wpp_url_desc' => 'Wirecard Payment Page v2 Adresse (URL WPP v2) (z.B. https://wpp.wirecard.com).',
    'wd_copy_xml_text' => 'XML kopieren',
    'wd_country' => 'Land',
    'wd_credit' => 'Rückerstatten',
    'wd_creditor' => 'Creditor',
    'wd_creditor_mandate_id' => 'Mandate ID',
    'wd_currency_config' => 'Jede Währung muss konfiguriert werden.',
    'wd_customerId' => 'Customer ID',
    'wd_date-of-birth' => 'Geburtsdatum',
    'wd_date_format_php_code' => 'd.m.Y',
    'wd_date_format_user_hint' => 'DD.MM.JJJJ',
    'wd_debtor' => 'Debitor',
    'wd_debtor_acc_owner' => 'Kontoinhaber',
    'wd_default_currency' => 'Standardwährung',
    'wd_descriptor' => 'Deskriptor',
    'wd_email' => 'E-Mail',
    'wd_enter_country_code_error' => 'Bitte geben Sie einen korrekten Ländercode ein.',
    'wd_enter_valid_email_error' => 'Geben Sie eine gültige E-Mail-Adresse ein.',
    'wd_error_credentials' => 'Test fehlgeschlagen. Überprüfen Sie Ihre Zugangsdaten.',
    'wd_error_save_failed' => 'Konfiguration ungültig. Speichern nicht möglich.',
    'wd_first-name' => 'Vorname',
    'wd_gender' => 'Geschlecht',
    'wd_heading_title' => 'Wirecard',
    'wd_heading_title_alipay_crossborder' => 'Alipay Cross-border',
    'wd_heading_title_creditcard' => 'Kartenzahlungen',
    'wd_heading_title_eps' => 'eps-Überweisung',
    'wd_heading_title_giropay' => 'giropay',
    'wd_heading_title_ideal' => 'iDEAL',
    'wd_heading_title_payolution_b2b' => 'Garantierter Kauf auf Rechnung (Payolution B2B)',
    'wd_heading_title_payolution_b2b_consumer' => 'Kauf auf Rechnung (Payolution B2B)',
    'wd_heading_title_payolution_invoice' => 'Garantierter Kauf auf Rechnung (Payolution B2C)',
    'wd_heading_title_payolution_invoice_consumer' => 'Kauf auf Rechnung (Payolution B2C)',
    'wd_heading_title_paypal' => 'PayPal',
    'wd_heading_title_pia' => 'Vorauskasse',
    'wd_heading_title_poi' => 'Kauf auf Rechnung',
    'wd_heading_title_ratepayinvoice' => 'Garantierter Kauf auf Rechnung mit Wirecard',
    'wd_heading_title_ratepayinvoice_consumer' => 'Kauf auf Rechnung mit Wirecard',
    'wd_heading_title_sepact' => 'SEPA-Überweisung',
    'wd_heading_title_sepadd' => 'SEPA-Lastschrift',
    'wd_heading_title_sofortbanking' => 'Sofort.',
    'wd_heading_title_support' => 'Wirecard Support',
    'wd_heading_title_transaction_details' => 'Wirecard Transaktionen',
    'wd_house-extension' => 'Adresszusatz',
    'wd_iban' => 'IBAN',
    'wd_ideal_legend' => 'Wählen Sie Ihre Bank',
    'wd_ip' => 'IP-Adresse',
    'wd_last-name' => 'Nachname',
    'wd_maid' => 'MAID',
    'wd_manipulated' => 'manipuliert',
    'wd_merchant-crm-id' => 'Merchant CRM ID',
    'wd_message_empty_error' => 'Nachricht darf nicht leer sein.',
    'wd_more_info' => 'Nähere Informationen',
    'wd_no' => 'Nein',
    'wd_orderNumber' => 'Bestellnummer',
    'wd_order_error' => 'Beim Bezahlprozess ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.',
    'wd_order_error_info' => 'Während dem Bezahlvorgang ist ein Fehler aufgetreten. Die Bestellung wurde gelöscht.',
    'wd_order_status' => 'Bestellstatus',
    'wd_order_status_authorized' => 'Autorisiert',
    'wd_order_status_cancelled' => 'Abgebrochen',
    'wd_order_status_failed' => 'Fehlgeschlagen',
    'wd_order_status_pending' => 'Ausstehend',
    'wd_order_status_purchased' => 'Bezahlt',
    'wd_order_status_refunded' => 'Rückerstattet',
    'wd_panel_action' => 'Aktion',
    'wd_panel_amount' => 'Betrag',
    'wd_panel_currency' => 'Währung',
    'wd_panel_details' => 'Details',
    'wd_panel_order_id' => 'Bestellreferenz',
    'wd_panel_order_number' => 'Bestellnummer',
    'wd_panel_parent_transaction_id' => 'Parent Transaction ID',
    'wd_panel_payment_method' => 'Zahlungsmittel',
    'wd_panel_provider_transaction_id' => 'Provider Transaction ID',
    'wd_panel_transaction' => 'Transaktion',
    'wd_panel_transaction_copy' => 'XML kopieren',
    'wd_panel_transaction_date' => 'Datum',
    'wd_panel_transaction_state' => 'Transaktionsstatus',
    'wd_panel_transcation_id' => 'Transaction ID',
    'wd_paymentMethod' => 'Zahlungsmittel',
    'wd_payment_awaiting' => 'Ausständige Zahlung von Wirecard.',
    'wd_payment_cancelled_text' => 'Die Zahlung wurde abgebrochen.',
    'wd_payment_cost' => 'Aufschlag für dieses Zahlungsmittel',
    'wd_payment_failed_text' => 'Bezahlvorgang fehlgeschlagen.',
    'wd_payment_method_settings' => 'Zahlungsmittel-Einstellungen',
    'wd_payment_refunded_text' => 'Die Zahlung wurde zurückerstattet.',
    'wd_payment_success_text' => 'Bezahlvorgang erfolgreich durchgeführt.',
    'wd_payolution_terms' => 'Mit der Übermittlung der für die Abwicklung des Rechnungskaufes sowie der Identitäts- und Bonitätsprüfung erforderlichen Daten an Payolution bin ich einverstanden. Meine <u><a href="%s" target="_blank">Einwilligung</a></u> kann ich jederzeit mit Wirkung für die Zukunft widerrufen.',
    'wd_phone' => 'Telefon',
    'wd_pia_ptrid' => 'Verwendungszweck',
    'wd_postal-code' => 'Postleitzahl',
    'wd_ptrid' => 'Provider Transaction Reference ID',
    'wd_ratepayinvoice_fields_error' => 'Für die Nutzung dieses Zahlungmittels müssen Sie mindestens 18 Jahre alt sein.',
    'wd_redirect_text' => 'Sie werden weitergeleitet. Bitte warten.',
    'wd_refund' => 'Rückerstatten',
    'wd_requestedAmount' => 'Betrag',
    'wd_requestId' => 'Request ID',
    'wd_save_to_user_account' => 'Daten in Ihrem Benutzerkonto speichern.',
    'wd_secured' => 'sicher',
    'wd_send_email' => 'Senden',
    'wd_sepa_mandate' => 'SEPA-Mandat',
    'wd_sepa_text_1' => 'Ich ermächtige den Creditor',
    'wd_sepa_text_2' => 'einmalig eine Zahlung von meinem Konto mittels SEPA-Lastschrift einzuziehen. Zugleich weise ich meine Bank an, die vom Creditor',
    'wd_sepa_text_2b' => 'auf mein Konto gezogene SEPA-Lastschrift einzulösen.',
    'wd_sepa_text_3' => 'Hinweis: Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrags verlangen. Es gelten dabei die mit meiner Bank vereinbarten Bedingungen.',
    'wd_sepa_text_4' => 'Für den Fall der Nichteinlösung der Lastschrift oder des Widerspruchs gegen die Lastschrift weise ich meine Bank unwiderruflich an, dem Creditor',
    'wd_sepa_text_5' => 'oder Dritten auf Anforderung meinen Namen, Adresse und Geburtsdatum vollständig mitzuteilen.',
    'wd_sepa_text_6' => 'Ich habe die Informationen zum SEPA-Lastschriftmandat gelesen und verstanden.',
    'wd_shipping-method' => 'Versandart',
    'wd_shipping_title' => 'Versand',
    'SHOP_MODULE_GROUP_wd_emails' => 'E-Mails',
    'SHOP_MODULE_wd_email_on_pending_orders' => 'E-Mail-Benachrichtigung für ausstehende Bestellungen senden.',
    'wd_social-security-number' => 'Sozialversicherungsnummer',
    'wd_state_awaiting' => 'ausstehend',
    'wd_state_closed' => 'beendet',
    'wd_state_error' => 'fehlerhaft',
    'wd_state_success' => 'erfolgreich',
    'wd_street1' => 'Straße',
    'wd_street2' => 'Straße 2',
    'wd_success_credentials' => 'Die Konfigurationseinstellungen wurden erfolgreich getestet.',
    'wd_success_email' => 'E-Mail wurde erfolgreich versendet.',
    'wd_support_description' => 'System-Informationen werden automatisch Ihrer Nachricht hinzugefügt und gesendet an',
    'wd_support_email_from' => 'Von',
    'wd_support_email_modules' => 'Andere Module',
    'wd_support_email_module_id' => 'Module ID',
    'wd_support_email_module_title' => 'Modul Titel',
    'wd_support_email_module_version' => 'Modul Version',
    'wd_support_email_php' => 'PHP Version',
    'wd_support_email_reply_to' => 'Antwort an',
    'wd_support_email_shop_edition' => 'OXID eShop Edition',
    'wd_support_email_shop_version' => 'OXID eShop Version',
    'wd_support_email_subject' => 'OXID eShop Support-Anfrage',
    'wd_support_email_system' => 'Server Info',
    'wd_support_send_error' => 'E-Mail an Support konnte nicht gesendet werden.',
    'wd_test_credentials' => 'Zugangsdaten testen',
    'wd_text_article_name' => 'Produktname',
    'wd_text_article_number' => 'Artikelnummer',
    'wd_text_backend_operations' => 'Mögliche Folgeoperationen',
    'wd_text_delete' => 'Löschen',
    'wd_text_generic_error' => 'Aktion fehlgeschlagen.',
    'wd_text_generic_success' => 'Aktion erfolgreich durchgeführt.',
    'wd_text_list' => 'Transaktionen',
    'wd_text_logo_variant_descriptive' => 'Deskriptiv',
    'wd_text_logo_variant_standard' => 'Standard',
    'wd_text_message' => 'Nachricht',
    'wd_text_no_data_available' => 'Keine Daten vorhanden.',
    'wd_text_no_further_operations_possible' => 'Keine weiteren Operationen verfügbar.',
    'wd_text_order_no_transactions' => 'Dieser Bestellung sind keine Transaktionen zugeordnet.',
    'wd_text_payment_action_pay' => 'Direktbuchung',
    'wd_text_payment_action_reserve' => 'Autorisierung',
    'wd_text_quantity' => 'Stück',
    'wd_text_support' => 'Support',
    'wd_text_vault' => 'One-Click-Checkout',
    'wd_three_d_link_text' => 'Limits mit/ohne 3D Secure',
    'wd_timeStamp' => 'Datum',
    'wd_total_amount_not_in_range_text' => 'Gesamtsumme außerhalb des erlaubten Bereichs.',
    'wd_transactionID' => 'Transaction ID',
    'wd_transactionState' => 'Transaktionsstatus',
    'wd_transactionType' => 'Transaktionstyp',
    'wd_transaction_details_title' => 'Transaktionsdetails',
    'wd_transaction_response_details' => 'Antwort-Details',
    'wd_transfer_notice' => 'Verwenden Sie für die Überweisung die folgenden Daten:',
    'wd_unmatched' => 'nicht zugeordnet',
    'wd_vault_changed_shipping_text' => 'Ihre Lieferadresse hat sich seit Ihrer letzten Bestellung geändert. Aus Sicherheitsgründen müssen Sie Ihre Kartendaten erneut eingeben.',
    'wd_vault_save_text' => 'Für spätere Verwendung speichern.',
    'wd_vault_use_new_text' => 'Neue Karte verwenden',
    'wd_wait_for_final_status' => 'Bitte warten Sie auf das separate E-Mail mit dem finalen Bezahlstatus.',
    'wd_warning_credit_card_url_mismatch' => 'Achtung: Bitte überprüfen Sie Ihre Konfigurationsdaten in den URL-Eingabefeldern. Möglicherweise haben Sie ein Produktivkonto mit einem Testkonto kombiniert.',
    'wd_yes' => 'Ja',
);
