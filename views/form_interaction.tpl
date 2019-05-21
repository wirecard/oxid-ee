[{*
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*}]

[{oxstyle include=$oViewConf->getPaymentGatewayUrl("out/css/wirecard_wdoxidee_spinner.css")}]

<html>
<head>
  <title>[{oxmultilang ident="wd_redirect_text"}]</title>
</head>
<body>
<div>
  <div class="loader" style="display:flex;justify-content:center;font-size:30px;margin-top:150px"></div>
  <div style="text-align:center;margin-top:200px">
    [{oxmultilang ident="wd_redirect_text"}]
  </div>
</div>
<form id="credit-card-form" method="[{$oView->getMethod()}]" action="[{$oView->getUrl()}]">
  [{assign var="mFormFields" value=$oView->getFormFields()}]
  [{foreach from=$mFormFields key=key item=value}]
<input type="hidden" name="[{$key}]" value="[{$value}]">
  [{/foreach}]
</form>
[{oxstyle}]
<script type="text/javascript">document.getElementById("credit-card-form").submit();</script>
</html>
