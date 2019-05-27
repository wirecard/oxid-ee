[{*
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*
*}]

[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

[{oxstyle include=$oViewConf->getPaymentGatewayUrl("out/css/wirecard_wdoxidee_common.css")}]

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="cl" value="[{$controller}]">

    <table cellspacing="0" cellpadding="0" border="0" width="600">
        [{if $message}]
        <tr>
            <td colspan="2">
                <div class="wdoxidee-messagebox wdoxidee-messagebox--[{$message.type}]">
                    [{$message.message}]
                </div>
            </td>
        </tr>
        [{/if}]
        [{if $actions|@count > 0}]
        <tr>
            <td width="25%">[{oxmultilang ident="wd_amount"}] ([{$currency}])</td>
            <td><input type="text" name="amount" pattern="^[0-9]*[\.,]?[0-9]+$" value="[{$requestParameters.amount}]" size="25"></td>
        </tr>
        <tr>
            <td colspan="2" height="50">
                [{foreach from=$actions key=key item=action}]
                <input type="submit" name="[{$key}]" value="[{$action.title}]">
                [{/foreach}]
            </td>
        </tr>
        [{else}]
            <div class="wdoxidee-messagebox wdoxidee-messagebox--info">
                [{$emptyText}]
            </div>
        [{/if}]
    </table>
</form>

[{if $message.type === 'success'}]
    <script type="text/javascript">
        top.oxid.admin.updateList();
    </script>
[{/if}]

[{if $oView->shouldDisplayLiveChat()}]
  [{include file="live_chat.tpl"}]
[{/if}]

[{include file="bottomnaviitem.tpl"}]

[{include file="bottomitem.tpl"}]
