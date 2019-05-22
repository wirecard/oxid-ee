[{$smarty.block.parent}]

[{if $order->isCustomPaymentMethod() }]
  <p>
    <strong>
      [{if $order->isPaymentPending() }]
        [{oxmultilang ident="wd_payment_awaiting"}]<br>
        [{oxmultilang ident="wd_wait_for_final_status"}]
      [{elseif $order->isPaymentSuccess() }]
        [{oxmultilang ident="wd_payment_success_text"}]
      [{elseif $order->isPaymentRefunded()}]
        [{oxmultilang ident="wd_payment_refunded_text"}]
      [{elseif $order->isPaymentCancelled()}]
        [{oxmultilang ident="wd_payment_cancelled_text"}]
      [{elseif $order->isPaymentFailed()}]
        [{oxmultilang ident="wd_order_error_info"}]
      [{else}]
        [{oxmultilang ident="wd_payment_awaiting"}]<br>
        [{oxmultilang ident="wd_wait_for_final_status"}]
      [{/if}]
    </strong>
  </p>
[{/if}]
