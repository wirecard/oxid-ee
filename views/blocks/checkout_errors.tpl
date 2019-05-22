[{*
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*
*}]

[{$smarty.block.parent}]

[{if $iPayError == -100}]
    <div class="alert alert-danger status error">[{oxmultilang ident="wd_canceled_payment_process"}]</div>
[{/if}]

[{if $iPayError == -101}]
    <div class="alert alert-danger status error">[{oxmultilang ident="wd_order_error"}]</div>
[{/if}]

[{if $iPayError == -102}]
    <div class="alert alert-danger status error">[{$oView->getPaymentErrorText()}]</div>
[{/if}]
