[{$smarty.block.parent}]

[{if $order->isCustomPaymentMethod() }]

  [{if $order->isPaymentPending() }]
    [{oxmultilang ident="payment_awaiting"}]
  [{elseif $order->isPaymentSuccess() }]
    [{oxmultilang ident="payment_success_text"}]
  [{else}]
    [{oxmultilang ident="order_error_info"}]
  [{/if}]

[{/if}]
