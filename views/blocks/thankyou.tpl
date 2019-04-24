[{*
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*
*}]

[{if $order->isCustomPaymentMethod()}]
    [{oxmultilang ident="THANK_YOU_FOR_ORDER"}] [{$oxcmp_shop->oxshops__oxname->value}]. <br>
    [{oxmultilang ident="REGISTERED_YOUR_ORDER" args=$order->oxorder__oxordernr->value}] <br><br>
    <strong>
        [{if $order->isPaymentPending()}]
            [{oxmultilang ident="wdpg_payment_awaiting" }]<br>
            [{if $sendPendingEmailsSettings }]
                [{oxmultilang ident="MESSAGE_YOU_RECEIVED_ORDER_CONFIRM"}]<br>
            [{/if}]
            [{oxmultilang ident="wdpg_wait_for_final_status"}]<br><br>
        [{else}]
            [{ oxmultilang ident="wdpg_payment_success_text" }]<br><br>
        [{/if}]
    </strong>
    [{oxmultilang ident="MESSAGE_WE_WILL_INFORM_YOU"}]<br><br>
[{else}]
    [{$smarty.block.parent}]
[{/if}]
