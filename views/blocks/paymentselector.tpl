[{if substr($paymentmethod->oxpayments__oxid, 0, 2) === "wd"}]
  [{include file="payment_other.tpl"}]
[{else}]
    [{$smarty.block.parent}]
[{/if}]
