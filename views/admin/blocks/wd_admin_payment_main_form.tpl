[{*
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*
*}]

[{oxstyle include=$oViewConf->getPaymentGatewayUrl("out/css/wirecard_wdoxidee_common.css")}]

[{if $edit && $edit->isCustomPaymentMethod()}]
  [{assign var="paymentMethod" value=$edit->getPaymentMethod()}]
  [{assign var="configFields" value=$paymentMethod->getConfigFields()}]
[{/if}]

[{if $configFields.apiUrl}]
  <style>
    .color-success {
      color: green;
    }

    .color-error {
      color: red;
    }

    .wd-validation {
      display: inline-block;
      margin-left: 12px;
    }

    .wd-multiselect {
      min-width: 150px;
    }

    .wd-separator {
      display: block;
      margin-top: 12px;
      font-size: 1.1em;
      font-weight: bold;
    }
  </style>

  [{oxscript include="js/libs/jquery.min.js"}]
  [{oxscript add="$.noConflict();"}]

  <script type="text/javascript">
    <!--
    function wdTestPaymentMethodCredentials(currency) {
      var $ = jQuery;

      var apiUrlFieldId = '#apiUrl';
      var httpUserFieldId = '#httpUser' + (currency ? '_' + currency : '');
      var httpPassFieldId = '#httpPassword' + (currency ? '_' + currency : '');
      var resultFieldId = '#testCredentials' + (currency ? '_' + currency : '') + '_validation';

      var elements = {
        apiUrl: $(apiUrlFieldId),
        httpUser: $(httpUserFieldId),
        httpPass: $(httpPassFieldId),
        result: $(resultFieldId)
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
        console.log('api url failed', elements.apiUrl.val());
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
        <div class="wdoxidee-messagebox wdoxidee-messagebox--error">
          [{oxmultilang ident="wd_error_save_failed"}]
        </div>
      </td>
    </tr>
  [{/if}]

  [{if $bShowCurrencyHelp}]
    <tr>
      <td colspan="1">
        <div class="wdoxidee-messagebox wdoxidee-messagebox--info">
          [{oxmultilang ident="wd_currency_config"}]
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
    <td height="40" valign="top">[{oxmultilang ident=$edit->oxpayments__initial_title->value}]</td>
  </tr>
[{/if}]

[{if $paymentMethod && $paymentMethod->isMerchantOnly()}]
  <tr>
    <td class="edittext" width="70">
      [{oxmultilang ident="GENERAL_ACTIVE"}]
    </td>
    <td class="edittext">
      <input
        class="edittext"
        type="checkbox"
        name="editval[oxpayments__oxactive]"
        value='1'
        [{if $edit->oxpayments__oxactive->value == 1}]checked[{/if}]
        [{$readonly}]
      >
      [{oxinputhelp ident="HELP_GENERAL_ACTIVE"}]
    </td>
  </tr>
  <tr>
    <td class="edittext" width="100">
      [{oxmultilang ident="PAYMENT_MAIN_NAME"}]
    </td>
    <td class="edittext">
      <input
        type="text"
        class="editinput"
        size="25"
        maxlength="[{$edit->oxpayments__oxdesc->fldmax_length}]"
        name="editval[oxpayments__oxdesc]"
        value="[{$edit->oxpayments__oxdesc->value}]"
        [{$readonly}]
      >
      [{oxinputhelp ident="HELP_PAYMENT_MAIN_NAME"}]
    </td>
  </tr>
[{else}]
  [{$smarty.block.parent}]
[{/if}]

[{if $configFields}]
  [{foreach from=$configFields key=configKey item=configField}]
    [{assign var="fieldName" value=$configField.field}]
    <tr>
      [{if $configField.title}]
        <td class="edittext [{if $configField.type === 'separator'}]wd-separator[{/if}]" width="70">[{$configField.title}]</td>
      [{/if}]
      <td class="edittext" [{if $configField.colspan}]colspan="[{$configField.colspan}]"[{/if}]>
        [{if $configField.type === 'text'}]
          <input
            id="[{$configKey}]"
            type="text"
            class="editinput"
            size="38"
            name="editval[[{$fieldName}]]"
            value="[{$edit->$fieldName->value}]"
            [{if $configField.placeholder}]placeholder="[{$configField.placeholder}]"[{/if}]
            [{if $configField.required}]required[{/if}]
            [{if $configField.onchange}]onchange="[{$configField.onchange}]"[{/if}]
          >
        [{/if}]

        [{if $configField.type === 'select'}]
          <select
            name="editval[[{$fieldName}]]"
            [{if $configField.required}]required[{/if}]
          >
            [{foreach from=$configField.options key=optionKey item=optionValue}]
              <option
                value="[{$optionKey}]"
                [{if $edit->$fieldName->value == $optionKey}]selected[{/if}]
              >[{$optionValue}]</option>
            [{/foreach}]
          </select>
        [{/if}]

        [{if $configField.type === 'multiselect'}]
          <select
            id="[{$configKey}]"
            name="editval[[{$fieldName}]][]"
            class="wd-multiselect"
            multiple
            [{if $configField.required}]required[{/if}]
          >
            [{foreach from=$configField.options key=optionKey item=optionValue}]
              <option
                  value="[{$optionKey}]"
                  [{if $edit->$fieldName->value && in_array($optionKey, $edit->$fieldName->value)}]selected[{/if}]
              >[{$optionValue}]</option>
            [{/foreach}]
          </select>
        [{/if}]

        [{if $configField.type === 'textarea'}]
          <textarea
            id="[{$configKey}]"
            class="editinput"
            rows="5"
            cols="37"
            name="editval[[{$fieldName}]]"
            value="[{$edit->$fieldName->value}]"
            [{if $configField.required}]required[{/if}]
          >[{$edit->$fieldName->value}]</textarea>
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

        <span id="[{$configKey}]_validation" class="wd-validation"></span>
      </td>
    </tr>
  [{/foreach}]
[{/if}]
