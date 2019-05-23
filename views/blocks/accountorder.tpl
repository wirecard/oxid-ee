[{*
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*
*}]

[{$smarty.block.parent}]<br/>

[{if $order->isCustomPaymentMethod()}]
  [{if $order->isPaymentPending()}]
    [{oxmultilang ident="wd_payment_awaiting"}]
  [{elseif $order->isPaymentSuccess()}]
    [{oxmultilang ident="wd_payment_success_text"}]
  [{elseif $order->isPaymentRefunded()}]
    [{oxmultilang ident="wd_payment_refunded_text"}]
  [{elseif $order->isPaymentCancelled()}]
    [{oxmultilang ident="wd_payment_cancelled_text"}]
  [{elseif $order->isPaymentFailed()}]
    [{oxmultilang ident="wd_payment_failed_text"}]
  [{else}]
    [{oxmultilang ident="wd_payment_awaiting"}]
  [{/if}]
[{/if}]
