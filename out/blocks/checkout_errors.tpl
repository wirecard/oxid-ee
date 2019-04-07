[{$smarty.block.parent}]

[{if $iPayError == -100}]
    <div class="alert alert-danger">[{oxmultilang ident="canceled_payment_process"}]</div>
[{/if}]

[{if $iPayError == -101}]
    <div class="alert alert-danger">[{oxmultilang ident="order_error"}]</div>
[{/if}]
