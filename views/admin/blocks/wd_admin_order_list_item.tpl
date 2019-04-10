[{if $listitem->oxorder__oxstorno->value == 1}]
    [{assign var="listclass" value=listitem3}]
[{else}]
    [{if $listitem->blacklist == 1}]
        [{assign var="listclass" value=listitem3}]
    [{else}]
        [{assign var="listclass" value=listitem$blWhite}]
    [{/if}]
[{/if}]
[{if $listitem->getId() == $oxid}]
    [{assign var="listclass" value=listitem4}]
[{/if}]

[{assign var="payment" value=$listitem->getOrderPayment()}]

<td valign="top" height="15" class="[{$listclass}]">
    <div class="listitemfloating">
        <a href="Javascript:top.oxid.admin.editThis('[{$listitem->oxorder__oxid->value}]');">[{$listitem->getTranslatedState()}]</a>
    </div>
</td>
<td valign="top" height="15" class="[{$listclass}]">
    <div class="listitemfloating">
        <a href="Javascript:top.oxid.admin.editThis('[{$listitem->oxorder__oxid->value}]');">[{$payment->oxpayments__oxdesc->value}]</a>
    </div>
</td>
<td valign="top" height="15" class="[{$listclass}]">
    <div class="listitemfloating">
        <a href="Javascript:top.oxid.admin.editThis('[{$listitem->oxorder__oxid->value}]');">[{$listitem->oxorder__wdoxidee_transactionid->value}]</a>
    </div>
</td>
<td valign="top" height="15" class="[{$listclass}]">
    <div class="listitemfloating">
        <a href="Javascript:top.oxid.admin.editThis('[{$listitem->oxorder__oxid->value}]');">[{$listitem->oxorder__wdoxidee_providertransactionid->value}]</a>
    </div>
</td>

[{$smarty.block.parent}]
