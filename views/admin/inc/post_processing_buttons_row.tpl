<tr>
  <td colspan="2" height="50">
    [{foreach from=$actions key=key item=action}]
  <input type="submit" name="[{$key}]" value="[{$action.title}]">
    [{/foreach}]
  </td>
</tr>
