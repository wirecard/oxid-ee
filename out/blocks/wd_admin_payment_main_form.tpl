[{if $edit && $edit->isCustomPaymentMethod()}]
  [{assign var="paymentMethod" value=$edit->getPaymentMethod()}]
  [{assign var="configFields" value=$paymentMethod->getConfigFields()}]
[{/if}]

[{if $configFields.apiUrl && $configFields.httpUser && $configFields.httpPassword}]
  <style>
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

      // the DOM elements that contain the actual config values
      var configApiUrlInput = document.getElementById('apiUrl');
      var configHttpUserInput = document.getElementById('httpUser');
      var configHttpPassInput = document.getElementById('httpPassword');

      // the DOM elements that are the labels for the config values
      var configApiUrlLabel = configApiUrlInput.parentNode.previousElementSibling;
      var configHttpUserLabel = configHttpUserInput.parentNode.previousElementSibling;
      var configHttpPassLabel = configHttpPassInput.parentNode.previousElementSibling;

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
          }
          if (!checkSuccess) {
            // additionally mark the labels red for easier visual identification
            addClassToElements([configApiUrlLabel, configHttpUserLabel, configHttpPassLabel], failureClassName);
          }
          showCheckResultText(checkSuccess);
        }
      };

      xhr.send(paramString);
    }
  </script>
[{/if}]

[{if $paymentMethod}]
  [{assign var="logoUrl" value=$edit->getLogoUrl()}]
  [{if $logoUrl}]
    <tr>
      <td><img src="[{$logoUrl}]" alt="[{$edit->oxpayments__oxdesc->value}]"></td>
    <tr>
  [{/if}]

  <tr>
    <td height="40" valign="top">[{ $edit->oxpayments__oxdesc->value }]</td>
  <tr>
[{/if}]

[{$smarty.block.parent}]

[{if $configFields}]
  [{foreach from=$configFields key=configKey item=configField}]
    [{assign var="fieldName" value=$configField.field}]
    <tr>
      <td class="edittext" width="70">[{$configField.title}]</td>
      <td class="edittext">
        [{if $configField.type === 'text'}]
          <input id="[{$configKey}]" type="text" class="editinput" size="25" name="editval[[{$fieldName}]]" value="[{$edit->$fieldName->value}]">
        [{/if}]

        [{if $configField.type === 'select'}]
          <select name="editval[[{$fieldName}]]">
            [{foreach from=$configField.options key=optionKey item=optionValue}]
              <option value="[{$optionKey}]" [{if $edit->$fieldName->value == $optionKey}]selected[{/if}]>[{$optionValue}]</option>
            [{/foreach}]
          </select>
        [{/if}]

        [{if $configField.description}]
          [{$oViewConf->getInputHelpHtml($configField.description)}]
        [{/if}]
      </td>
    </tr>

    [{if $configKey === 'httpPassword' && $configFields.apiUrl && $configFields.httpUser}]
      <tr>
        <td class="edittext" width="100">
          <input type="button" value="[{oxmultilang ident="test_credentials"}]" onclick="testPaymentMethodCredentials()">
        </td>
        <td>
          <span id="test_credentials_result"></span>
        </td>
      </tr>
    [{/if}]
  [{/foreach}]
[{/if}]
