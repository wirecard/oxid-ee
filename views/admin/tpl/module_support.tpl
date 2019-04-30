[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
  [{$oViewConf->getHiddenSid()}]
  <input type="hidden" name="oxid" value="[{$oxid}]">
  <input type="hidden" name="cl" value="wcpg_module_support">
</form>

[{if $isOurModule && $contactEmail}]
  [{if $alertMessage}]
  <tr>
    <td colspan="2">
      <div class="messagebox">
        <div [{if $alertType === 'error'}]class="warning"[{/if}] [{if $alertType === 'success'}]class="success"[{/if}]>[{$alertMessage}]</div>
      </div>
    </td>
  </tr>
  [{/if}]

  <form action="[{$oViewConf->getSelfLink()}]" method="post">
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="cl" value="wcpg_module_support">
    <input type="hidden" name="fnc" value="sendSupportEmailAction">

    <h3>[{oxmultilang ident="wd_heading_title_support"}]</h3>

    <p>[{oxmultilang ident="wd_support_description"}]: <strong>[{$contactEmail}]</strong></p>
    <br>
    <table cellspacing="0" cellpadding="0" border="0" width="98%">
      <tbody>
      <tr>
        <td class="edittext" width="70">
          [{oxmultilang ident="wd_config_email"}]
        </td>
        <td class="edittext">
          <input type="text" name="module_support_email_from" class="editinput" value="[{$fromEmail}]" placeholder="[{$defaultEmail}]" size="40"/>
        </td>
      </tr>
      <tr>
        <td class="edittext" width="70">
          [{oxmultilang ident="wd_config_reply_to"}]
        </td>
        <td class="edittext">
          <input type="text" name="module_support_email_reply" class="editinput" value="[{$replyToEmail}]" placeholder="[{$defaultEmail}]" size="40"/>
        </td>
      </tr>
      <tr>
        <td class="edittext" width="70">
          [{oxmultilang ident="wd_config_message"}]
        </td>
        <td class="edittext">
          <textarea name="module_support_text" class="editinput" rows="6" cols="40">[{$body}]</textarea>
        </td>
      </tr>
      <tr>
        <td>
          <input type="submit" value="[{oxmultilang ident="wd_send_email"}]" />
        </td>
      </tr>
      </tbody>
    </table>
  </form>
[{/if}]

[{include file="bottomitem.tpl"}]
