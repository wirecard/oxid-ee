[{if $order->isCustomPaymentMethod()}]
    [{if $order->isPaymentPending()}]
        [{ oxmultilang ident="payment_awaiting" }]<br/><br/>
    [{else}]
        [{ oxmultilang ident="payment_success_text" }]<br/><br/>
    [{/if}]
[{/if}]

[{$smarty.block.parent}]
