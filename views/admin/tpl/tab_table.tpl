[{*
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*
*}]

<style>
    .wd-table {
        width: 100%;
        border-spacing: 0;
        border-collapse: collapse;
        overflow: hidden;
    }

    .wd-table th,
    .wd-table td {
        padding: 4px 8px;
        border: 1px solid #ddd;
    }

    .wd-table th {
        font-size: 12px;
        text-align: inherit;
        text-transform: uppercase;
    }
</style>

[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="cl" value="[{$controller}]">
</form>

[{if $data}]
    <table class="wd-table">
        [{if $data.head}]
            <thead>
                [{foreach from=$data.head item=cell}]
                    <th [{if $cell.nowrap}]class="nowrap"[{/if}]>[{$cell.text}]</th>
                [{/foreach}]
            </thead>
        [{/if}]

        [{if $data.body}]
            <tbody>
                [{foreach from=$data.body item=row}]
                    <tr>
                        [{foreach from=$row item=cell}]
                            [{assign var="indent" value=$cell.indent*30}]
                            <td [{if $cell.nowrap}]class="nowrap"[{/if}] [{if $indent}]style="border-left-width:[{$indent}]px;"[{/if}]>[{$cell.text}]</td>
                        [{/foreach}]
                    </tr>
                [{/foreach}]
            </tbody>
        [{/if}]

        [{if $data.foot}]
            <tfoot>
                [{foreach from=$data.foot item=cell}]
                    <td [{if $cell.nowrap}]class="nowrap"[{/if}]>[{$cell.text}]</td>
                [{/foreach}]
            </tfoot>
        [{/if}]
    </table>
[{elseif $emptyText}]
    <em>[{$emptyText}]</em>
[{/if}]

[{if $oView->shouldDisplayLiveChat()}]
  [{include file="live_chat.tpl"}]
[{/if}]

[{include file="bottomnaviitem.tpl"}]

[{include file="bottomitem.tpl"}]
