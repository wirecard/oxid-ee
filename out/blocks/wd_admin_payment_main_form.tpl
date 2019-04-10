[{if $edit && $edit->isCustomPaymentMethod()}]
  [{assign var="paymentMethod" value=$edit->getPaymentMethod()}]
  [{assign var="configFields" value=$paymentMethod->getConfigFields()}]
[{/if}]

[{if $configFields.apiUrl && $configFields.httpUser && $configFields.httpPassword}]
  <style>
    .color-success {
      color: green;
    }

    .color-error {
      color: red;
    }
  </style>

  [{oxscript include="js/libs/jquery.min.js"}]
  [{oxscript add="$.noConflict();"}]

  <script type="text/javascript">
    <!--
    function wdTestPaymentMethodCredentials() {
      var $ = jQuery;
      var elements = {
        apiUrl: $('#apiUrl'),
        httpUser: $('#httpUser'),
        httpPass: $('#httpPassword'),
        result: $('#test_credentials_result')
      };

      elements.labels = $()
        .add(elements.apiUrl.parent().prev())
        .add(elements.httpUser.parent().prev())
        .add(elements.httpPass.parent().prev());

      elements.result
        .html('')
        .add(elements.labels)
        .removeClass('color-success color-error');

      // check if API URL is a root URL, if not there is no need to perform the request
      if (!/^https\:\/\/[^\/]+$/.test(elements.apiUrl.val())) {
        elements.result
          .text('[{oxmultilang ident="error_credentials"}]')
          .add(elements.labels.eq(0))
          .addClass('color-error');

        return;
      }

      // perform AJAX request to check credential validity
      $.ajax({
        url: '[{ $oViewConf->getAjaxLink() }]cmpid=container&container=payment_main&fnc=checkPaymentMethodCredentials',
        method: 'POST',
        data: {
          apiUrl: elements.apiUrl.val(),
          httpUser: elements.httpUser.val(),
          httpPass: elements.httpPass.val()
        },
        dataType: 'json'
      })
      .done(function (data) {
        if (data && data.success) {
          elements.result
            .text('[{oxmultilang ident="success_credentials"}]')
            .addClass('color-success');
        } else {
          elements.result
            .text('[{oxmultilang ident="error_credentials"}]')
            .add(elements.labels)
            .addClass('color-error');
        }
      });
    }
    //-->
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
          <input type="button" value="[{oxmultilang ident="test_credentials"}]" onclick="wdTestPaymentMethodCredentials()">
        </td>
        <td>
          <span id="test_credentials_result"></span>
        </td>
      </tr>
    [{/if}]
  [{/foreach}]
[{/if}]
