[{*
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*
*}]

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
                                <input class="listedit" type="text" size="20" name="where[wdoxidee_ordertransactions][transactionnumber]" value="[{$where.wdoxidee_ordertransactions.transactionnumber}]">
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
                                <input class="listedit" type="text" size="20" name="where[wdoxidee_ordertransactions][type]" value="[{$where.wdoxidee_ordertransactions.type}]">
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
                    <td class="listheader first" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'wdoxidee_ordertransactions', 'transactionnumber', 'desc');document.search.submit();">[{oxmultilang ident="wd_panel_transaction"}]</a></td>
                    <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'wdoxidee_ordertransactions', 'orderid', 'asc');document.search.submit();">[{oxmultilang ident="wd_panel_order_id"}]</a></td>
                    <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'wdoxidee_ordertransactions', 'ordernumber', 'asc');document.search.submit();">[{oxmultilang ident="wd_panel_order_number"}]</a></td>
                    <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'wdoxidee_ordertransactions', 'transactionid', 'asc');document.search.submit();">[{oxmultilang ident="wd_panel_transcation_id"}]</a></td>
                    <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'wdoxidee_ordertransactions', 'parenttransactionid', 'asc');document.search.submit();">[{oxmultilang ident="wd_panel_parent_transaction_id"}]</a></td>
                    <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'wdoxidee_ordertransactions', 'type', 'asc');document.search.submit();">[{oxmultilang ident="wd_panel_action"}]</a></td>
                    <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'oxpayments', 'oxdesc', 'asc');document.search.submit();">[{oxmultilang ident="wd_panel_payment_method"}]</a></td>
                    <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'wdoxidee_ordertransactions', 'state', 'asc');document.search.submit();">[{oxmultilang ident="wd_panel_transaction_state"}]</a></td>
                    <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'wdoxidee_ordertransactions', 'amount', 'asc');document.search.submit();">[{oxmultilang ident="wd_panel_amount"}]</a></td>
                    <td class="listheader" height="15"><a href="Javascript:top.oxid.admin.setSorting(document.search, 'wdoxidee_ordertransactions', 'currency', 'asc');document.search.submit();">[{oxmultilang ident="wd_panel_currency"}]</a></td>
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
                        <a href="Javascript:top.oxid.admin.editThis('[{$listitem->wdoxidee_ordertransactions__oxid->value}]');">[{$listitem->wdoxidee_ordertransactions__transactionnumber->value}]</a>
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
                    <td class="[{$listclass}]"><a href="Javascript:top.oxid.admin.editThis('[{$listitem->wdoxidee_ordertransactions__oxid->value}]');">[{$listitem->getTranslatedTransactionType()}]</a>
                    </td>
                    <td class="[{$listclass}]">
                        <a href="Javascript:top.oxid.admin.editThis('[{$listitem->wdoxidee_ordertransactions__oxid->value}]');">[{$payment->oxpayments__oxdesc->value}]</a>
                    </td>
                    <td class="[{$listclass}]">
                        <a href="Javascript:top.oxid.admin.editThis('[{$listitem->wdoxidee_ordertransactions__oxid->value}]');">[{$listitem->getTranslatedState()}]
                          [{if $listitem->isPoiPiaPaymentMethod()}]
                            [{if $listitem->wdoxidee_ordertransactions__parenttransactionid->value}]
                                ([{oxmultilang ident="wd_matched"}])
                            [{else}]
                                ([{oxmultilang ident="wd_unmatched"}])
                            [{/if}]
                          [{/if}]
                        </a>
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
    parent.parent.sMenuItem    = "[{oxmultilang ident="wd_heading_title_transaction_details"}]";
    parent.parent.sMenuSubItem = "[{oxmultilang ident="wd_text_list"}]";
    parent.parent.sWorkArea    = "[{$_act}]";
    parent.parent.setTitle();
}
</script>

</body>
</html>
