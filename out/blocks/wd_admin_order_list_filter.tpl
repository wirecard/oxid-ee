<td valign="top" class="listfilter" height="20">
    <div class="r1">
        <div class="b1">
            <select name="where[oxorder][wdoxidee_orderstate]" onChange="document.search.submit();">
                <option value="" style="color: #000000;">[{oxmultilang ident="ORDER_LIST_FOLDER_ALL"}]</option>

                [{foreach from=$orderStates key=state item=name}]
                <option value="[{$state}]" [{if $where.oxorder.wdoxidee_orderstate == $state}]selected[{/if}]>[{$name}]</option>
                [{/foreach}]
            </select>
        </div>
    </div>
</td>
<td valign="top" class="listfilter" height="20">
    <div class="r1">
        <div class="b1">
            <input class="listedit" type="text" size="20" name="where[oxorder][wdoxidee_transactionid]" value="[{$where.oxorder.wdoxidee_transactionid}]">
        </div>
    </div>
</td>

[{$smarty.block.parent}]
