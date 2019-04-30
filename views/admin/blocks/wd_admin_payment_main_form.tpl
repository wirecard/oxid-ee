[{*
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*
*}]

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

    .pg-validation {
      display: inline-block;
      margin-left: 12px;
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
        result: $('#testCredentials_validation')
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
          .text('[{oxmultilang ident="wd_error_credentials"}]')
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
              .text('[{oxmultilang ident="wd_success_credentials"}]')
              .addClass('color-success');
          } else {
            elements.result
              .text('[{oxmultilang ident="wd_error_credentials"}]')
              .add(elements.labels)
              .addClass('color-error');
          }
        });
    }

    function wdCheckCountryCode() {
      var $ = jQuery;
      var elements = {
        countryCode: $('#countryCode'),
        result: $('#countryCode_validation')
      };

      elements.result
        .html('')
        .add(elements.countryCode.parent().prev())
        .removeClass('color-success color-error');

      if (!/^[a-z]{2}_[a-z]{2}$/.test(elements.countryCode.val())) {
        elements.result
          .text('[{oxmultilang ident="wd_enter_country_code_error"}]')
          .add(elements.countryCode.parent().prev())
          .addClass('color-error');
      }
    }
    //-->
  </script>
  [{/if}]

[{if $paymentMethod}]
  [{if $bConfigNotValid}]
    <tr>
      <td colspan="2">
        <div class="messagebox">
          <div class="warning">[{oxmultilang ident="wd_error_save_failed"}]</div>
        </div>
      </td>
    </tr>
  [{/if}]

  [{assign var="logoUrl" value=$edit->getLogoUrl()}]
  [{if $logoUrl}]
  <tr>
    <td><img src="[{$logoUrl}]" alt="[{$edit->oxpayments__oxdesc->value}]"></td>
  </tr>
  [{/if}]

  <tr>
    <td height="40" valign="top">[{ $edit->oxpayments__oxdesc->value }]</td>
  </tr>
  [{/if}]

[{$smarty.block.parent}]

[{if $configFields}]
  [{foreach from=$configFields key=configKey item=configField}]
    [{assign var="fieldName" value=$configField.field}]
    <tr>
      [{if $configField.title}]
        <td class="edittext" width="70">[{$configField.title}]</td>
      [{/if}]
      <td class="edittext" [{if $configField.colspan}]colspan="[{$configField.colspan}]"[{/if}]>
        [{if $configField.type === 'text'}]
          <input id="[{$configKey}]" type="text" class="editinput" size="38"
                 name="editval[[{$fieldName}]]" value="[{$edit->$fieldName->value}]"
                 [{if $configField.onchange}]onchange="[{$configField.onchange}]"[{/if}]
                 / >
        [{/if}]

        [{if $configField.type === 'select'}]
          <select name="editval[[{$fieldName}]]">
            [{foreach from=$configField.options key=optionKey item=optionValue}]
              <option value="[{$optionKey}]" [{if $edit->$fieldName->value == $optionKey}]selected[{/if}]>[{$optionValue}]</option>
            [{/foreach}]
          </select>
        [{/if}]

        [{if $configField.type === 'link'}]
          <a target="_blank" href="[{$configField.link}]">[{$configField.text}]</a>
        [{/if}]

        [{if $configField.type === 'button'}]
          <input type="button" value="[{$configField.text}]" onclick="[{$configField.onclick}]" />
        [{/if}]

        [{if $configField.description}]
          [{$oViewConf->getInputHelpHtml($configField.description)}]
        [{/if}]

        <span id="[{$configKey}]_validation" class="pg-validation"></span>
      </td>
    </tr>
  [{/foreach}]
[{/if}]
