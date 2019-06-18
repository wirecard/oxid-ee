<div style="margin-top:15px">
[{foreach from=$actions key=key item=action}]
<input type="submit" name="[{$key}]" value="[{$action.title}]">
  [{/foreach}]
</div>
