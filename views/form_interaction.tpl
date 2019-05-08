[{*
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*}]
<html>
<head>
  <title>[{oxmultilang ident="wd_redirect_text"}]</title>
</head>
<div>
  [{oxmultilang ident="wd_redirect_text"}]
</div>
<form id="credit-card-form" method="[{$oView->getMethod()}]" action="[{$oView->getUrl()}]">
  [{assign var="mFormFields" value=$oView->getFormFields()}]
  [{foreach from=$mFormFields key=key item=value}]
<input type="hidden" name="[{$key}]" value="[{$value}]">
  [{/foreach}]
</form>
<script type="text/javascript">document.getElementById("credit-card-form").submit();</script>
</html>
