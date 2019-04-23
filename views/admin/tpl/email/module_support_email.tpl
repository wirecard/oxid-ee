[{assign var="shop"      value=$oEmailView->getShop()}]
[{assign var="oViewConf" value=$oEmailView->getViewConfig()}]
[{assign var="userinfo"  value=$oEmailView->getUser()}]
[{assign var="headerTemplate" value="`$shopTemplateDir`email/html/header.tpl"}]

[{include file=$headerTemplate title=$emailData.subject}]

<table border="0" width="100%" cellspacing="10" cellpadding="2" bgcolor="#FFFFFF" class="column">
  <tr>
    <td class="text-pad" style="white-space:nowrap;">
      <b>[{oxmultilang ident="support_email_from"}]</b>
    </td>
    <td style="white-space:nowrap;">[{$shop->oxshops__oxname->getRawValue()|oxescape}]</td>
  </tr>
  <tr>
    <td class="text-pad" style="white-space:nowrap;">
      <b>[{oxmultilang ident="EMAIL"}]</b>
    </td>
    <td style="white-space:nowrap;">[{$emailData.from|oxescape}]</td>
  </tr>
  [{if $emailData.replyTo }]
    <tr>
      <td class="text-pad" style="white-space:nowrap;">
        <b>[{oxmultilang ident="support_email_reply_to"}]</b>
      </td>
      <td style="white-space:nowrap;">[{$emailData.replyTo|oxescape}]</td>
    </tr>
  [{/if}]
  <tr>
    <td class="text-pad" style="white-space:nowrap;">
      <b>[{oxmultilang ident="text_message"}]</b>
    </td>
    <td>[{$emailData.body|oxescape|nl2br}]</td>
  </tr>
  <tr>
    <td class="text-pad" colspan="2">&nbsp;</td>
  </tr>
  <tr>
    <td class="text-pad" style="white-space:nowrap;">
      <b>[{oxmultilang ident="support_email_shop_version"}]</b>
    </td>
    <td>[{$emailData.shopVersion|oxescape}]</td>
  </tr>
  <tr>
    <td class="text-pad" style="white-space:nowrap;">
      <b>[{oxmultilang ident="support_email_shop_edition"}]</b>
    </td>
    <td>[{$emailData.shopEdition|oxescape}]</td>
  </tr>
  <tr>
    <td class="text-pad" style="white-space:nowrap;">
      <b>[{oxmultilang ident="support_email_module_title"}]</b>
    </td>
    <td>[{$emailData.module->getTitle()|oxescape}]</td>
  </tr>
  <tr>
    <td class="text-pad" style="white-space:nowrap;">
      <b>[{oxmultilang ident="support_email_module_version"}]</b>
    </td>
    <td>[{$emailData.module->getInfo('version')|oxescape}]</td>
  </tr>
  <tr>
    <td class="text-pad" style="white-space:nowrap;">
      <b>[{oxmultilang ident="support_email_php"}]</b>
    </td>
    <td>[{$emailData.phpVersion|oxescape}]</td>
  </tr>
  <tr>
    <td class="text-pad" style="white-space:nowrap;">
      <b>[{oxmultilang ident="support_email_system"}]</b>
    </td>
    <td>[{$emailData.system|oxescape}]</td>
  </tr>
  <tr>
    <td class="text-pad" colspan="2">&nbsp;</td>
  </tr>
  <tr>
    <td class="text-pad" colspan="2">
      <b style="white-space:nowrap;">[{oxmultilang ident="support_email_modules"}]</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <table>
        <tr>
          <th class="text-pad">
            [{oxmultilang ident="support_email_module_id"}]
          </th>
          <th class="text-pad">
            [{oxmultilang ident="support_email_module_title"}]
          </th>
          <th class="text-pad">
            [{oxmultilang ident="support_email_module_version"}]
          </th>
          <th class="text-pad">
            [{oxmultilang ident="GENERAL_ACTIVE"}]
          </th>
        </tr>
        [{foreach from=$emailData.modules item=module}]
          <tr>
            <td class="text-pad" style="white-space:nowrap;">
              [{$module->getId()}]
            </td>
            <td class="text-pad">
              [{$module->getTitle()}]
            </td>
            <td class="text-pad">
              [{$module->getInfo('version')}]
            </td>
            <td class="text-pad" style="white-space:nowrap;">
              [{if $module->isActive()}]
                [{oxmultilang ident="yes"}]
              [{/if}]
            </td>
          </tr>
        [{/foreach}]
      </table>
    </td>
  </tr>
  <tr>
    <td class="text-pad" colspan="2">&nbsp;</td>
  </tr>
  <tr>
    <td class="text-pad" colspan="2">
      <b style="white-space:nowrap;">[{oxmultilang ident="payment_method_settings"}]</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <table>
        [{* List all payments *}]
        [{foreach from=$emailData.payments item=payment}]
          [{assign var="paymentMethod" value=$payment->getPaymentMethod()}]
          <tr>
            <th class="text-pad" style="white-space:nowrap;" colspan="2">
              [{$payment->oxpayments__oxdesc->value}]
            </th>
          </tr>
          <tr>
            <td class="text-pad" style="white-space:nowrap;">
              [{oxmultilang ident="GENERAL_ACTIVE"}]
            </td>
            <td class="text-pad">
              [{if $payment->oxpayments__oxactive->value}]
                [{oxmultilang ident="yes"}]
              [{else}]
                [{oxmultilang ident="no"}]
              [{/if}]
            </td>
          </tr>
          [{* List all support fields of payment *}]
          [{foreach from=$paymentMethod->getSupportConfigFields() item=configField}]
            [{assign var="fieldName" value=$configField.field}]
            <tr>
              <td class="text-pad" style="white-space:nowrap;">
                [{$configField.title}]
              </td>
              <td class="text-pad">
                [{if $configField.type == 'text' }]
                  [{$payment->$fieldName->value}]
                [{/if}]
                [{if $configField.type == 'select'}]
                  [{foreach from=$configField.options key=optionKey item=optionValue}]
                    [{if $payment->$fieldName->value == $optionKey}]
                      [{$optionValue}]
                    [{/if}]
                  [{/foreach}]
                [{/if}]
              </td>
            </tr>
          [{/foreach}]
          <tr>
            <td class="text-pad" colspan="2">&nbsp;</td>
          </tr>
        [{/foreach}]
      </table>
    </td>
  </tr>
</table>

</td></tr></table></center></td></tr></table></body></html>
