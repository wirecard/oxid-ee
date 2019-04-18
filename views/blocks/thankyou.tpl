[{*
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*
*}]

[{if $order->isCustomPaymentMethod()}]
    [{if $order->isPaymentPending()}]
        [{ oxmultilang ident="payment_awaiting" }]<br/><br/>
    [{else}]
        [{ oxmultilang ident="payment_success_text" }]<br/><br/>
    [{/if}]
[{/if}]

[{$smarty.block.parent}]
