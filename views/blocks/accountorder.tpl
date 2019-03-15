[{$smarty.block.parent}]<br/>

[{if $order->isCustomPaymentMethod()}]
    [{if $order->isPaymentPending()}]
        [{ oxmultilang ident="payment_awaiting" }]
    [{elseif $order->isPaymentSuccess()}]
        [{ oxmultilang ident="payment_success_text" }]
    [{else}]
        [{ oxmultilang ident="payment_failed_text" }]
    [{/if}]
[{/if}]
