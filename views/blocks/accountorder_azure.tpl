[{*
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*
*}]

[{$smarty.block.parent}]<br/>

[{if $order->isCustomPaymentMethod() && $order->isLastArticle($orderitem)}]
    [{if $order->isPaymentPending()}]
        [{ oxmultilang ident="payment_awaiting" }]
    [{elseif $order->isPaymentSuccess()}]
        [{ oxmultilang ident="payment_success_text" }]
    [{else}]
        [{ oxmultilang ident="payment_failed_text" }]
    [{/if}]
[{/if}]
