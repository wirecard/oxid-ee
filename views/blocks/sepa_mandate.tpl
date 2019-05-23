[{*
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*
*}]

<style>
  .mt-30 {
    margin-top: 30px;
  }
  .mb-30 {
    margin-bottom: 30px;
  }
</style>

<h3>[{oxmultilang ident="wd_sepa_mandate"}]</h3>

<hr>

<i>[{oxmultilang ident="wd_creditor"}]</i>

<p class="mb-30">
  [{$sCreditorName}]<br>
  [{$oShop->oxshops__oxstreet}]<br>
  [{$oShop->oxshops__oxzip}] [{$oShop->oxshops__oxcity}]<br>
  [{$oShop->oxshops__oxcountry}]<br>
  [{oxmultilang ident="wd_config_creditor_id"}] [{$oPayment->oxpayments__wdoxidee_creditorid->value}]<br>
  [{oxmultilang ident="wd_creditor_mandate_id"}] [{$sMandateId}]
</p>

<i>[{oxmultilang ident="wd_debtor"}]</i>

<p class="mb-30">
  [{oxmultilang ident="wd_debtor_acc_owner"}] [{$sAccountHolder}]<br>
  [{oxmultilang ident="wd_iban"}] [{$sIban}]
  [{if $sBic}]
    <br>
    [{oxmultilang ident="wd_bic"}] [{$sBic}]
  [{/if}]
</p>

[{$sCustomSepaMandate|nl2br}]

<p class="mt-30">[{$sConsumerCity}], [{$sDate}] [{$sAccountHolder}]</p>
