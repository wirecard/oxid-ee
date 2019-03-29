<html>
<head>
  <title>[{oxmultilang ident="redirect_text"}]</title>
</head>
<div>
  [{oxmultilang ident="redirect_text"}]
</div>
<form id="credit-card-form" method="[{$oView->getMethod()}]" action="[{$oView->getUrl()}]">
  [{assign var="mFormFields" value=$oView->getFormFields()}]
  [{foreach from=$mFormFields key=key item=value}]
<input type="hidden" name="[{$key}]" value="[{$value}]">
  [{/foreach}]
</form>
<script type="text/javascript">document.getElementById("credit-card-form").submit();</script>
</html>
