[{*
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*
*}]

<td class="listheader" height="15">
    <a href="Javascript:top.oxid.admin.setSorting(document.search, 'oxorder', 'wdoxidee_orderstate', 'asc');document.search.submit();" class="listheader">
        [{oxmultilang ident="order_status"}]
    </a>
</td>
<td class="listheader" height="15">
    <a href="Javascript:top.oxid.admin.setSorting(document.search, 'oxorder', 'wdoxidee_oxpaymenttype', 'asc');document.search.submit();" class="listheader">
        [{oxmultilang ident="panel_payment_method"}]
    </a>
</td>
<td class="listheader" height="15">
    <a href="Javascript:top.oxid.admin.setSorting(document.search, 'oxorder', 'wdoxidee_transactionid', 'asc');document.search.submit();" class="listheader">
        [{oxmultilang ident="panel_transcation_id"}]
    </a>
</td>
<td class="listheader" height="15">
    <a href="Javascript:top.oxid.admin.setSorting(document.search, 'oxorder', 'wdoxidee_providertransactionid', 'asc');document.search.submit();" class="listheader">
        [{oxmultilang ident="panel_provider_transaction_id"}]
    </a>
</td>

[{$smarty.block.parent}]
