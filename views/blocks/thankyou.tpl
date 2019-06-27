[{*
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*
*}]

[{assign var="paymentInAdvanceInfo" value=$oView->getPaymentInAdvanceInfo()}]

[{if $paymentInAdvanceInfo && $paymentInAdvanceInfo->sIban}]
    <style>
        #pia-payment-info {
            margin-bottom: 20px;
        }
        #pia-payment-info,
        #pia-payment-info td {
            border: 1px solid lightgray;
        }
        #pia-payment-info td {
            padding: 8px 10px;
        }
        #pia-payment-info tr > td:first-child {
            font-weight: 600;
        }
    </style>
[{/if}]

[{if $order->isCustomPaymentMethod()}]
    [{oxmultilang ident="THANK_YOU_FOR_ORDER"}] [{$oxcmp_shop->oxshops__oxname->value}]. <br>
    [{oxmultilang ident="REGISTERED_YOUR_ORDER" args=$order->oxorder__oxordernr->value}] <br><br>

    [{if $paymentInAdvanceInfo && $paymentInAdvanceInfo->sIban}]
        <table id="pia-payment-info">
            <tr>
                <td colspan="2">[{oxmultilang ident="wd_transfer_notice"}]</td>
            </tr>
            <tr>
                <td>[{oxmultilang ident="wd_amount"}]</td>
                <td>[{$paymentInAdvanceInfo->sAmount}]</td>
            </tr>
            <tr>
                <td>[{oxmultilang ident="wd_iban"}]</td>
                <td>[{$paymentInAdvanceInfo->sIban}]</td>
            </tr>
            <tr>
                <td>[{oxmultilang ident="wd_bic"}]</td>
                <td>[{$paymentInAdvanceInfo->sBic}]</td>
            </tr>
            <tr>
                <td>[{oxmultilang ident="wd_ptrid"}]</td>
                <td>[{$paymentInAdvanceInfo->sTransactionRefId}]</td>
            </tr>
        </table>
    [{/if}]

    <strong>
        [{if $order->isPaymentPending()}]
            [{oxmultilang ident="wd_payment_awaiting" }]<br>
            [{if $sendPendingEmailsSettings }]
                [{oxmultilang ident="MESSAGE_YOU_RECEIVED_ORDER_CONFIRM"}]<br>
            [{/if}]
            [{oxmultilang ident="wd_wait_for_final_status"}]<br><br>
        [{elseif $order->isPaymentSuccess() }]
            [{oxmultilang ident="wd_payment_success_text"}]<br><br>
        [{else}]
            [{oxmultilang ident="wd_payment_awaiting" }]<br>
            [{if $sendPendingEmailsSettings }]
                [{oxmultilang ident="MESSAGE_YOU_RECEIVED_ORDER_CONFIRM"}]<br>
            [{/if}]
            [{oxmultilang ident="wd_wait_for_final_status"}]<br><br>
      [{/if}]
    </strong>
    [{oxmultilang ident="MESSAGE_WE_WILL_INFORM_YOU"}]<br><br>
[{else}]
    [{$smarty.block.parent}]
[{/if}]
