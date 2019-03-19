<style>
    .pb-35 {
        padding-bottom: 35px;
    }
</style>

<script type="text/javascript">
  function testPaymentMethodCredentials() {
    // the DOM element the test result will be rendered to
    var resultSpan = document.getElementById('test_credentials_result');

    // clear any previous test results
    resultSpan.innerHTML = '';

    var bodyParams = {
      apiUrl: document.getElementById('configApiUrl').value,
      httpUser: document.getElementById('configHttpUser').value,
      httpPass: document.getElementById('configHttpPass').value
    };

    var successText = '[{oxmultilang ident="success_credentials"}]';
    var failureText = '[{oxmultilang ident="error_credentials"}]';

    var requestUrl = '[{ $oViewConf->getAjaxLink() }]cmpid=container&container=payment_main&fnc=checkPaymentMethodCredentials';

    var paramString = Object.keys(bodyParams).map(function(key) {
      return encodeURIComponent(key) + '=' + encodeURIComponent(bodyParams[key]);
    }).join('&');

    var xhr = new XMLHttpRequest();
    xhr.open('POST', requestUrl);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

    xhr.onreadystatechange = function() {
      var DONE = 4;     // XMLHttpRequest finished state
      var OK = 200;     // HTTP OK status code

      if (xhr.readyState === DONE) {
        if (xhr.status === OK) {
          var response = JSON.parse(xhr.responseText);

          resultSpan.innerHTML = response && response.success === true ? successText : failureText;
        } else {
          resultSpan.innerHTML = failureText;
        }
      }
    };

    xhr.send(paramString);
  }
</script>

[{if $edit->oxpayments__wdoxidee_iswirecard->value == 1}]
  <tr>
    <td>
      [{assign var="oShopConf" value=$oViewConf->getConfig()}]
      <img src="[{$oShopConf->getShopUrl()}]modules/wirecard/paymentgateway/out/img/[{ $edit->oxpayments__wdoxidee_logo->value }]">
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
        <input type="text" class="editinput" size="25" id="configApiUrl" name="editval[oxpayments__wdoxidee_apiurl]" value="[{$edit->oxpayments__wdoxidee_apiurl}]">
        [{oxinputhelp ident="config_base_url_desc"}]
    </td>
  </tr>
  <tr>
    <td class="edittext" width="70">
      [{oxmultilang ident="config_http_user"}]
    </td>
    <td class="edittext">
        <input type="text" class="editinput" size="25" id="configHttpUser" name="editval[oxpayments__wdoxidee_httpuser]" value="[{$edit->oxpayments__wdoxidee_httpuser}]">
    </td>
  </tr>
  <tr>
    <td class="edittext" width="70">
      [{oxmultilang ident="config_http_password"}]
    </td>
    <td class="edittext">
        <input type="text" class="editinput" size="25" id="configHttpPass" name="editval[oxpayments__wdoxidee_httppass]" value="[{$edit->oxpayments__wdoxidee_httppass}]">
    </td>
  </tr>
  <tr>
    <td class="edittext" width="100">
      <input type="button" value="[{oxmultilang ident="test_credentials"}]" onclick="testPaymentMethodCredentials()" />
    </td>
    <td>
      <span id="test_credentials_result"></span>
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

  [{if $edit->oxpayments__oxid->value == "wdcreditcard"}]
    <tr>
      <td class="edittext" width="70">
        [{oxmultilang ident="config_three_d_merchant_account_id"}]
      </td>
      <td class="edittext">
          <input type="text" class="editinput" size="25" name="editval[oxpayments__wdoxidee_three_d_maid]" value="[{$edit->oxpayments__wdoxidee_three_d_maid}]">
          [{oxinputhelp ident="config_three_d_merchant_account_id_desc"}]
      </td>
    </tr>
    <tr>
      <td class="edittext" width="70">
        [{oxmultilang ident="config_three_d_merchant_secret"}]
      </td>
      <td class="edittext">
          <input type="text" class="editinput" size="25" name="editval[oxpayments__wdoxidee_three_d_secret]" value="[{$edit->oxpayments__wdoxidee_three_d_secret}]">
          [{oxinputhelp ident="config_three_d_merchant_secret_desc"}]
      </td>
    </tr>
    <tr>
      <td class="edittext" width="70">
        [{oxmultilang ident="config_ssl_max_limit"}]
      </td>
      <td class="edittext">
          <input type="text" class="editinput" size="25" name="editval[oxpayments__wdoxidee_non_three_d_max_limit]" value="[{$edit->oxpayments__wdoxidee_non_three_d_max_limit}]">
          [{oxinputhelp ident="config_ssl_max_limit_desc"}]
      </td>
    </tr>
    <tr>
      <td class="edittext" width="70">
        [{oxmultilang ident="config_three_d_min_limit"}]
      </td>
      <td class="edittext">
          <input type="text" class="editinput" size="25" name="editval[oxpayments__wdoxidee_three_d_min_limit]" value="[{$edit->oxpayments__wdoxidee_three_d_min_limit}]">
          [{oxinputhelp ident="config_three_d_min_limit_desc"}]
      </td>
    </tr>
    <tr>
      <td class="edittext" width="70">
        [{oxmultilang ident="default_currency"}]
      </td>
      <td class="edittext">
        <select name="editval[oxpayments__wdoxidee_default_currency]">
          [{foreach from=$currencies item=currency}]
            <option value="[{$currency->name}]" [{if $currency->name == $edit->oxpayments__wdoxidee_default_currency->value}]SELECTED[{/if}]>[{$currency->name}]</option>
          [{/foreach}]
        </select>
      </td>
    </tr>
  [{/if}]
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
