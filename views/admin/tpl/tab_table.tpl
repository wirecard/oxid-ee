[{*
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*
*}]

[{oxstyle include=$oViewConf->getPaymentGatewayUrl("out/css/wirecard_wdoxidee_table.css")}]

[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="cl" value="[{$controller}]">
</form>

[{if $data}]
  [{include file="table.tpl"}]
[{elseif $emptyText}]
    <em>[{$emptyText}]</em>
[{/if}]

[{if $oView->shouldDisplayLiveChat()}]
  [{include file="live_chat.tpl"}]
[{/if}]

[{include file="bottomnaviitem.tpl"}]

[{include file="bottomitem.tpl"}]
