[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

<script type="text/javascript">
<!--
function copyToClipboard(text)
{
    var textarea = document.createElement('textarea');
    var success = false;

    // old versions of IE
    if (window.clipboardData) {
        success = window.clipboardData.setData('Text', decodeURIComponent(text));
    // other browsers
    } else {
        textarea.textContent = decodeURIComponent(text);
        textarea.style.position = 'fixed';

        document.body.appendChild(textarea);
        textarea.select();

        try {
            success = document.execCommand('copy');
        } catch (e) {
            success = false;
        }

        document.body.removeChild(textarea);
    }

    return success;
}
//-->
</script>

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="cl" value="[{$controller}]">
</form>

<table cellspacing="0" cellpadding="0" border="0" width="600">
    [{foreach from=$listData item=row}]
        <tr height="20">
            <td width="25%">[{$row.title}]</td>
            <td>
                [{if $row.action === 'copyToClipboard'}]
                <button type="button" onclick="copyToClipboard('[{$row.value|escape:'url'}]');">[{$row.action_title}]</button>
                [{else}]
                <strong>[{$row.value}]</strong>
                [{/if}]
            </td>
        </tr>
    [{/foreach}]
</table>

[{include file="bottomnaviitem.tpl"}]

[{include file="bottomitem.tpl"}]
