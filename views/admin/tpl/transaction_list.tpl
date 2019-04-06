[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign box="list"}]
[{assign var="where" value=$oView->getListFilter()}]

<script type="text/javascript">
<!--
window.onload = function ()
{
    top.reloadEditFrame();
    [{if $updatelist == 1}]
        top.oxid.admin.updateList('[{$oxid}]');
    [{/if}]
}
//-->
</script>

<div id="liste">
    <form name="search" id="search" action="[{$oViewConf->getSelfLink()}]" method="post">
        [{include file="_formparams.tpl" cl="wcpg_transaction_list" lstrt=$lstrt actedit=$actedit oxid=$oxid fnc="" language=$actlang editlanguage=$actlang}]

        <table cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr>
                [{block name="admin_transaction_list_filter"}]
                    <td class="listfilter first">
                        <div class="r1">
                            <div class="b1">
                                <input class="listedit" type="text" size="20" name="where[wdoxidee_ordertransactions][oxid]" value="[{$where.wdoxidee_ordertransactions.oxid}]">
                            </div>
                        </div>
                    </td>
                    <td class="listfilter">
                        <div class="r1">
                            <div class="b1">
                                <input class="listedit" type="text" size="20" name="where[wdoxidee_ordertransactions][orderid]" value="[{$where.wdoxidee_ordertransactions.orderid}]">
                            </div>
                        </div>
                    </td>
                    <td class="listfilter">
                        <div class="r1">
                            <div class="b1">
                                <input class="listedit" type="text" size="20" name="where[wdoxidee_ordertransactions][ordernumber]" value="[{$where.wdoxidee_ordertransactions.ordernumber}]">
                            </div>
                        </div>
                    </td>
                    <td class="listfilter">
                        <div class="r1">
                            <div class="b1">
                                <input class="listedit" type="text" size="20" name="where[wdoxidee_ordertransactions][transactionid]" value="[{$where.wdoxidee_ordertransactions.transactionid}]">
                            </div>
                        </div>
                    </td>
                    <td class="listfilter">
                        <div class="r1">
                            <div class="b1">
                                <input class="listedit" type="text" size="20" name="where[wdoxidee_ordertransactions][parenttransactionid]" value="[{$where.wdoxidee_ordertransactions.parenttransactionid}]">
                            </div>
                        </div>
                    </td>
                    <td class="listfilter">
                        <div class="r1">
                            <div class="b1">
                                <select name="where[wdoxidee_ordertransactions][action]" onChange="document.search.submit();">
                                    <option value="" style="color: #000000;">[{oxmultilang ident="ORDER_LIST_FOLDER_ALL"}]</option>
                                    [{foreach from=$actions item=action}]
                                    <option value="[{$action}]" [{if $where.wdoxidee_ordertransactions.action === $action}]selected[{/if}]>[{$action}]</option>
                                    [{/foreach}]
                                </select>
                            </div>
                        </div>
                    </td>
                    <td class="listfilter">
                        <div class="r1">
                            <div class="b1">
                                <select name="where[oxpayments][oxid]" onChange="document.search.submit();">
                                    <option value="" style="color: #000000;">[{oxmultilang ident="ORDER_LIST_FOLDER_ALL"}]</option>
                                    [{foreach from=$payments item=payment}]
                                    <option value="[{$payment->getId()}]" [{if $where.oxpayments.oxid === $payment->getId()}]selected[{/if}]>[{$payment->oxpayments__oxdesc->value}]</option>
                                    [{/foreach}]
                                </select>
                            </div>
                        </div>
                    </td>
                    <td class="listfilter">
                        <div class="r1">
                            <div class="b1">
                                <select name="where[wdoxidee_ordertransactions][state]" onChange="document.search.submit();">
                                    <option value="" style="color: #000000;">[{oxmultilang ident="ORDER_LIST_FOLDER_ALL"}]</option>
                                    [{foreach from=$states item=state}]
                                    <option value="[{$state}]" [{if $where.wdoxidee_ordertransactions.state === $state}]selected[{/if}]>[{$state}]</option>
                                    [{/foreach}]
                                </select>
                            </div>
                        </div>
                    </td>
                    <td class="listfilter">
                        <div class="r1">
                            <div class="b1">
                                <input class="listedit" type="text" size="20" name="where[wdoxidee_ordertransactions][amount]" value="[{$where.wdoxidee_ordertransactions.amount}]">
                            </div>
                        </div>
                    </td>
                    <td class="listfilter">
                        <div class="r1">
                            <div class="b1">
                                <input class="listedit" type="text" size="3" name="where[wdoxidee_ordertransactions][currency]" value="[{$where.wdoxidee_ordertransactions.currency}]">
                                <div class="find"><input class="listedit" type="submit" name="submitit" value="[{oxmultilang ident="GENERAL_SEARCH"}]"></div>
                            </div>
                        </div>
                    </td>
                [{/block}]
            </tr>

            <tr>
                [{block name="admin_transaction_list_sorting"}]
                    <td class="listheader first" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'wdoxidee_ordertransactions', 'oxid', 'asc');document.search.submit();">[{oxmultilang ident="panel_transaction"}]</a></td>
                    <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'wdoxidee_ordertransactions', 'orderid', 'asc');document.search.submit();">[{oxmultilang ident="panel_order_id"}]</a></td>
                    <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'wdoxidee_ordertransactions', 'ordernumber', 'asc');document.search.submit();">[{oxmultilang ident="panel_order_number"}]</a></td>
                    <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'wdoxidee_ordertransactions', 'transactionid', 'asc');document.search.submit();">[{oxmultilang ident="panel_transcation_id"}]</a></td>
                    <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'wdoxidee_ordertransactions', 'parenttransactionid', 'asc');document.search.submit();">[{oxmultilang ident="panel_parent_transaction_id"}]</a></td>
                    <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'wdoxidee_ordertransactions', 'action', 'asc');document.search.submit();">[{oxmultilang ident="panel_action"}]</a></td>
                    <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'oxpayments', 'oxdesc', 'asc');document.search.submit();">[{oxmultilang ident="panel_payment_method"}]</a></td>
                    <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'wdoxidee_ordertransactions', 'state', 'asc');document.search.submit();">[{oxmultilang ident="panel_transaction_state"}]</a></td>
                    <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'wdoxidee_ordertransactions', 'amount', 'asc');document.search.submit();">[{oxmultilang ident="panel_amount"}]</a></td>
                    <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'wdoxidee_ordertransactions', 'currency', 'asc');document.search.submit();">[{oxmultilang ident="panel_currency"}]</a></td>
                [{/block}]
            </tr>

            [{foreach from=$mylist item=listitem}]
            <tr>
                [{block name="admin_transaction_list_item"}]
                    [{assign var="order" value=$listitem->getTransactionOrder()}]
                    [{assign var="payment" value=$order->getOrderPayment()}]
                    [{if $listitem->getId() == $oxid}]
                        [{assign var="listclass" value=listitem4}]
                    [{else}]
                        [{assign var="listclass" value=null}]
                    [{/if}]
                    <td class="[{$listclass}]">
                        <a href="Javascript:top.oxid.admin.editThis('[{$listitem->wdoxidee_ordertransactions__oxid->value}]');">[{$listitem->wdoxidee_ordertransactions__oxid->value}]</a>
                    </td>
                    <td class="[{$listclass}]">
                        <a href="Javascript:top.oxid.admin.editThis('[{$listitem->wdoxidee_ordertransactions__oxid->value}]');">[{$listitem->wdoxidee_ordertransactions__orderid->value}]</a>
                    </td>
                    <td class="[{$listclass}]">
                        <a href="Javascript:top.oxid.admin.editThis('[{$listitem->wdoxidee_ordertransactions__oxid->value}]');">[{$listitem->wdoxidee_ordertransactions__ordernumber->value}]</a>
                    </td>
                    <td class="[{$listclass}]">
                        <a href="Javascript:top.oxid.admin.editThis('[{$listitem->wdoxidee_ordertransactions__oxid->value}]');">[{$listitem->wdoxidee_ordertransactions__transactionid->value}]</a>
                    </td>
                    <td class="[{$listclass}]">
                        <a href="Javascript:top.oxid.admin.editThis('[{$listitem->wdoxidee_ordertransactions__oxid->value}]');">[{$listitem->wdoxidee_ordertransactions__parenttransactionid->value}]</a>
                    </td>
                    <td class="[{$listclass}]">
                        <a href="Javascript:top.oxid.admin.editThis('[{$listitem->wdoxidee_ordertransactions__oxid->value}]');">[{$listitem->wdoxidee_ordertransactions__action->value}]</a>
                    </td>
                    <td class="[{$listclass}]">
                        <a href="Javascript:top.oxid.admin.editThis('[{$listitem->wdoxidee_ordertransactions__oxid->value}]');">[{$payment->oxpayments__oxdesc->value}]</a>
                    </td>
                    <td class="[{$listclass}]">
                        <a href="Javascript:top.oxid.admin.editThis('[{$listitem->wdoxidee_ordertransactions__oxid->value}]');">[{$listitem->wdoxidee_ordertransactions__state->value}]</a>
                    </td>
                    <td class="[{$listclass}]">
                        <a href="Javascript:top.oxid.admin.editThis('[{$listitem->wdoxidee_ordertransactions__oxid->value}]');">[{$listitem->wdoxidee_ordertransactions__amount->value}]</a>
                    </td>
                    <td class="[{$listclass}]">
                        <a href="Javascript:top.oxid.admin.editThis('[{$listitem->wdoxidee_ordertransactions__oxid->value}]');">[{$listitem->wdoxidee_ordertransactions__currency->value}]</a>
                    </td>
                [{/block}]
            </tr>
            [{/foreach}]

            [{include file="pagenavisnippet.tpl" colspan="9"}]
        </table>
    </form>
</div>

[{include file="pagetabsnippet.tpl"}]

<script type="text/javascript">
if (parent.parent) {
    parent.parent.sShopTitle   = "[{$actshopobj->oxshops__oxname->getRawValue()|oxaddslashes}]";
    parent.parent.sMenuItem    = "[{oxmultilang ident="heading_title_transaction_details"}]";
    parent.parent.sMenuSubItem = "[{oxmultilang ident="text_list"}]";
    parent.parent.sWorkArea    = "[{$_act}]";
    parent.parent.setTitle();
}
</script>

</body>
</html>
