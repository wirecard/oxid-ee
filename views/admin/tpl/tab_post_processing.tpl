[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="cl" value="[{$controller}]">

    <table cellspacing="0" cellpadding="0" border="0" width="600">
        [{if $alert}]
        <tr>
            <td colspan="2">
                <div class="messagebox">
                    <div [{if $alert.type === 'error'}]class="warning"[{/if}]>[{$alert.message}]</div>
                </div>
            </td>
        </tr>
        [{/if}]
        [{if $actions|@count > 0}]
        <tr>
            <td width="25%">[{oxmultilang ident="amount"}] ([{$currency}])</td>
            <td><input type="text" name="amount" value="[{$requestParameters.amount}]" size="25"></td>
        </tr>
        <tr>
            <td colspan="2" height="50">
                [{foreach from=$actions key=key item=action}]
                <input type="submit" name="[{$key}]" value="[{$action.title}]">
                [{/foreach}]
            </td>
        </tr>
        [{else}]
            <div class="messagebox">
                [{$noOperationsAvailableString}]
            </div>
        [{/if}]
    </table>
</form>

[{if $alert.type === 'success'}]
    <script type="text/javascript">
        top.oxid.admin.updateList();
    </script>
[{/if}]

[{include file="bottomnaviitem.tpl"}]

[{include file="bottomitem.tpl"}]
