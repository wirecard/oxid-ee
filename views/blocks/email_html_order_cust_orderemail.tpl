[{$smarty.block.parent}]

[{if $order->isCustomPaymentMethod() }]
  <p>
    <strong>
      [{if $order->isPaymentPending() }]
        [{oxmultilang ident="wdpg_payment_awaiting"}]<br>
        [{oxmultilang ident="wdpg_wait_for_final_status"}]
      [{elseif $order->isPaymentSuccess() }]
        [{oxmultilang ident="wdpg_payment_success_text"}]
      [{else}]
        [{oxmultilang ident="wdpg_order_error_info"}]
      [{/if}]
    </strong>
  </p>
[{/if}]
