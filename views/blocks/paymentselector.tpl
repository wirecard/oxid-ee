[{if $oViewConf->isWirecardPaymentMethod($paymentmethod->oxpayments__oxid->value)}]
  [{include file="payment_other_with_logo.tpl"}]
[{else}]
    [{$smarty.block.parent}]
[{/if}]
