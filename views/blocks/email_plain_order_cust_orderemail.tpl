[{$smarty.block.parent}]

[{if $order->isCustomPaymentMethod() }]

  [{if $order->isPaymentPending() }]
    [{oxmultilang ident="wdpg_payment_awaiting"}]
    [{oxmultilang ident="wdpg_wait_for_final_status"}]
  [{elseif $order->isPaymentSuccess() }]
    [{oxmultilang ident="wdpg_payment_success_text"}]
  [{else}]
    [{oxmultilang ident="wdpg_order_error_info"}]
  [{/if}]

[{/if}]
