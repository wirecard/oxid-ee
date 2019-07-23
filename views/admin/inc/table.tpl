[{oxstyle include=$oViewConf->getPaymentGatewayUrl("out/css/wirecard_wdoxidee_table.css")}]
<table class="wd-table">
[{if $data.head}]
  <thead>
  [{foreach from=$data.head item=cell}]
    <th [{if $cell.nowrap}]class="nowrap" [{/if}]>[{$cell.text}]</th>
    [{/foreach}]
  </thead>
  [{/if}]

[{if $data.body}]
  <tbody>
  [{foreach from=$data.body item=row}]
    <tr>
      [{foreach from=$row item=cell}]
      [{assign var="indent" value=$cell.indent*30}]
      <td [{if $cell.nowrap}]class="nowrap" [{/if}] [{if $indent}]style="border-left-width:[{$indent}]px;" [{/if}]>
        [{$cell.text}]
      </td>
      [{/foreach}]
    </tr>
    [{/foreach}]
  </tbody>
  [{/if}]

[{if $data.foot}]
  <tfoot>
  [{foreach from=$data.foot item=cell}]
    <td [{if $cell.nowrap}]class="nowrap" [{/if}]>[{$cell.text}]</td>
    [{/foreach}]
  </tfoot>
  [{/if}]
</table>

