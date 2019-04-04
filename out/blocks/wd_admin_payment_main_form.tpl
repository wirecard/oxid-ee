<style>
    .pb-35 {
        padding-bottom: 35px;
    }

    .color-success {
      color: green;
    }

    .color-failure {
      color: red;
    }
</style>

<script type="text/javascript">
  function addClassToElements(elements, className) {
    elements.forEach(function(element) {
      element.classList.add(className);
    });
  }

  function removeClassFromElements(elements, className) {
    elements.forEach(function(element) {
      // if no class name was passed, remove all classes from the element
      if (!className) {
        element.className = '';
      } else {
        element.classList.remove(className);
      }
    });
  }

  function testPaymentMethodCredentials() {
    var checkSuccess = false;

    var successText = '[{oxmultilang ident="success_credentials"}]';
    var failureText = '[{oxmultilang ident="error_credentials"}]';

    var successClassName = 'color-success';
    var failureClassName = 'color-failure';

    // the DOM element the test result will be rendered to
    var resultSpan = document.getElementById('test_credentials_result');

    // the DOM elements that are the labels for the configu values
    var configApiUrlLabel = document.getElementById('labelConfigApiUrl');
    var configHttpUserLabel = document.getElementById('labelConfigHttpUser');
    var configHttpPassLabel = document.getElementById('labelConfigHttpPass');

    // the DOM elements that contain the actual config values
    var configApiUrlInput = document.getElementById('configApiUrl');
    var configHttpUserInput = document.getElementById('configHttpUser');
    var configHttpPassInput = document.getElementById('configHttpPass');

    var showCheckResultText = function(success) {
      resultSpan.innerHTML = success ? successText : failureText;
      resultSpan.classList.add(success ? successClassName : failureClassName);
    };

    // clear any previous test results
    resultSpan.innerHTML = '';
    removeClassFromElements([resultSpan, configApiUrlLabel, configHttpUserLabel, configHttpPassLabel]);

    // check if API URL is a root URL, if not there is no need to perform the request
    var regexCheck = /^https\:\/\/[^\/]+$/;

    if (!regexCheck.test(configApiUrlInput.value)) {
      showCheckResultText(checkSuccess);
      addClassToElements([configApiUrlLabel], failureClassName);
      return;
    }

    // build check configuration request
    var requestUrl = '[{ $oViewConf->getAjaxLink() }]cmpid=container&container=payment_main&fnc=checkPaymentMethodCredentials';

    var bodyParams = {
      apiUrl: configApiUrlInput.value,
      httpUser: configHttpUserInput.value,
      httpPass: configHttpPassInput.value
    };

    var paramString = Object.keys(bodyParams).map(function(key) {
      return encodeURIComponent(key) + '=' + encodeURIComponent(bodyParams[key]);
    }).join('&');

    // perform AJAX request to check credential validity
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

          checkSuccess = response && response.success === true;

          if (!checkSuccess) {
            // additionally mark the labels red for easier visual identification
            addClassToElements([configApiUrlLabel, configHttpUserLabel, configHttpPassLabel], failureClassName);
          }
        }

        showCheckResultText(checkSuccess);
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
    <td class="edittext" width="70" id="labelConfigApiUrl">
      [{oxmultilang ident="config_base_url"}]
    </td>
    <td class="edittext">
        <input type="text" class="editinput" size="40" id="configApiUrl" name="editval[oxpayments__wdoxidee_apiurl]" value="[{$edit->oxpayments__wdoxidee_apiurl}]">
        [{oxinputhelp ident="config_base_url_desc"}]
    </td>
  </tr>
  <tr>
    <td class="edittext" width="70" id="labelConfigHttpUser">
      [{oxmultilang ident="config_http_user"}]
    </td>
    <td class="edittext">
        <input type="text" class="editinput" size="40" id="configHttpUser" name="editval[oxpayments__wdoxidee_httpuser]" value="[{$edit->oxpayments__wdoxidee_httpuser}]">
    </td>
  </tr>
  <tr>
    <td class="edittext" width="70" id="labelConfigHttpPass">
      [{oxmultilang ident="config_http_password"}]
    </td>
    <td class="edittext">
        <input type="text" class="editinput" size="40" id="configHttpPass" name="editval[oxpayments__wdoxidee_httppass]" value="[{$edit->oxpayments__wdoxidee_httppass}]">
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
        <input type="text" class="editinput" size="40" name="editval[oxpayments__wdoxidee_maid]" value="[{$edit->oxpayments__wdoxidee_maid}]">
        [{oxinputhelp ident="config_three_d_merchant_account_id_desc"}]
    </td>
  </tr>
  <tr>
    <td class="edittext" width="70">
      [{oxmultilang ident="config_merchant_secret"}]
    </td>
    <td class="edittext">
        <input type="text" class="editinput" size="40" name="editval[oxpayments__wdoxidee_secret]" value="[{$edit->oxpayments__wdoxidee_secret}]">
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
