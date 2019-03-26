<style>
    .pb-35 {
        padding-bottom: 35px;
    }
</style>

[{if $edit->oxpayments__wdoxidee_iswirecard->value == 1}]
  <tr>
    <td>
      <img src="/modules/wirecard/paymentgateway/out/img/[{ $edit->oxpayments__wdoxidee_logo->value }]">
    </td>
  <tr>
  <tr>
    <td class="pb-35">
      [{ $edit->oxpayments__oxdesc->value }]
    </td>
  <tr>
[{/if}]

[{$smarty.block.parent}]

[{if $edit->oxpayments__wdoxidee_iswirecard->value == 1}]
  <tr>
    <td class="edittext" width="70">
      [{oxmultilang ident="config_base_url"}]
    </td>
    <td class="edittext">
        <input type="text" class="editinput" size="25" name="editval[oxpayments__wdoxidee_apiurl]" value="[{$edit->oxpayments__wdoxidee_apiurl}]">
        [{oxinputhelp ident="config_base_url_desc"}]
    </td>
  </tr>
  <tr>
    <td class="edittext" width="70">
      [{oxmultilang ident="config_http_user"}]
    </td>
    <td class="edittext">
        <input type="text" class="editinput" size="25" name="editval[oxpayments__wdoxidee_httpuser]" value="[{$edit->oxpayments__wdoxidee_httpuser}]">
    </td>
  </tr>
  <tr>
    <td class="edittext" width="70">
      [{oxmultilang ident="config_http_password"}]
    </td>
    <td class="edittext">
        <input type="text" class="editinput" size="25" name="editval[oxpayments__wdoxidee_httppass]" value="[{$edit->oxpayments__wdoxidee_httppass}]">
    </td>
  </tr>
  <tr>
    <td class="edittext" width="70">
      [{oxmultilang ident="config_merchant_account_id"}]
    </td>
    <td class="edittext">
        <input type="text" class="editinput" size="25" name="editval[oxpayments__wdoxidee_maid]" value="[{$edit->oxpayments__wdoxidee_maid}]">
        [{oxinputhelp ident="config_three_d_merchant_account_id_desc"}]
    </td>
  </tr>
  <tr>
    <td class="edittext" width="70">
      [{oxmultilang ident="config_merchant_secret"}]
    </td>
    <td class="edittext">
        <input type="text" class="editinput" size="25" name="editval[oxpayments__wdoxidee_secret]" value="[{$edit->oxpayments__wdoxidee_secret}]">
        [{oxinputhelp ident="config_three_d_merchant_secret_desc"}]
    </td>
  </tr>
  <tr>
    <td class="edittext" width="70">
      [{oxmultilang ident="config_shopping_basket"}]
    </td>
    <td class="edittext">
      <select name="editval[oxpayments__wdoxidee_basket]">
        <option value="1" [{if $edit->oxpayments__wdoxidee_basket->value == 1}]selected[{/if}]>
          [{oxmultilang ident="yes"}]
        </option>
        <option value="0" [{if $edit->oxpayments__wdoxidee_basket->value == 0}]selected[{/if}]>
          [{oxmultilang ident="no"}]
        </option>
      </select>
      [{oxinputhelp ident="config_shopping_basket_desc"}]
    </td>
  </tr>
  <tr>
    <td class="edittext" width="70">
      [{oxmultilang ident="config_descriptor"}]
    </td>
    <td class="edittext">
      <select name="editval[oxpayments__wdoxidee_descriptor]">
        <option value="1" [{if $edit->oxpayments__wdoxidee_descriptor->value == 1}]selected[{/if}]>
          [{oxmultilang ident="yes"}]
        </option>
        <option value="0" [{if $edit->oxpayments__wdoxidee_descriptor->value == 0}]selected[{/if}]>
          [{oxmultilang ident="no"}]
        </option>
      </select>
      [{oxinputhelp ident="config_descriptor_desc"}]
    </td>
  </tr>
  <tr>
    <td class="edittext" width="70">
      [{oxmultilang ident="config_additional_info"}]
    </td>
    <td class="edittext">
      <select name="editval[oxpayments__wdoxidee_additional_info]">
        <option value="1" [{if $edit->oxpayments__wdoxidee_additional_info->value == 1}]selected[{/if}]>
          [{oxmultilang ident="yes"}]
        </option>
        <option value="0" [{if $edit->oxpayments__wdoxidee_additional_info->value == 0}]selected[{/if}]>
          [{oxmultilang ident="no"}]
        </option>
      </select>
      [{oxinputhelp ident="config_additional_info_desc"}]
    </td>
  </tr>
  <tr>
    <td class="edittext" width="70">
      [{oxmultilang ident="config_payment_action"}]
    </td>
    <td class="edittext">
      <select name="editval[oxpayments__wdoxidee_transactiontype]">
        <option value="authorize-capture" [{if $edit->oxpayments__wdoxidee_transactiontype->value == 'authorize-capture'}]selected[{/if}]>
            [{oxmultilang ident="text_payment_action_reserve"}]
          </option>
          <option value="purchase" [{if $edit->oxpayments__wdoxidee_transactiontype->value == 'purchase'}]selected[{/if}]>
            [{oxmultilang ident="text_payment_action_pay"}]
          </option>
      </select>
      [{oxinputhelp ident="config_payment_action_desc"}]
    </td>
  </tr>
[{/if}]
