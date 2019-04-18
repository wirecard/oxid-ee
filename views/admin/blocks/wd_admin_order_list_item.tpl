[{*
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*
*}]

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
