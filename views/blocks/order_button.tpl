[{oxstyle include=$oViewConf->getPaymentGatewayUrl("out/css/wirecard_wdoxidee_modal.css")}]
[{oxscript include=$oViewConf->getPaymentGatewayUrl('out/js/wirecard_wdoxidee_sepadd.js') priority=9}]

[{assign var="payment" value=$oView->getPayment()}]
[{if $payment->oxpayments__oxid->value == "wdsepadd"}]
    <div class="well well-sm cart-buttons">
        <button id="openMandateModal" class="btn btn-lg btn-primary pull-right submitButton nextStep largeButton">
            [{oxmultilang ident="SUBMIT_ORDER"}]
        </button>
        <div class="clearfix"></div>
    </div>
[{else}]
    [{$smarty.block.parent}]
[{/if}]

[{include file="sepa_mandate_modal.tpl"}]
