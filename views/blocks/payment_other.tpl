<dl>
    <dt>
        [{assign var="oShopConf" value=$oViewConf->getConfig()}]
        <input id="payment_[{$sPaymentID}]" type="radio" name="paymentid" value="[{$sPaymentID}]" [{if $oView->getCheckedPaymentId() == $paymentmethod->oxpayments__oxid->value}]checked[{/if}]>
        <label for="payment_[{$sPaymentID}]"><b>[{$paymentmethod->oxpayments__oxdesc->value}]</b></label>
    </dt>
    <dd class="[{if $oView->getCheckedPaymentId() == $paymentmethod->oxpayments__oxid->value}]activePayment[{/if}]">
        [{if $paymentmethod->getPrice()}]
            [{assign var="oPaymentPrice" value=$paymentmethod->getPrice() }]
            [{if $oViewConf->isFunctionalityEnabled('blShowVATForPayCharge') }]
                [{strip}]
                    ([{oxprice price=$oPaymentPrice->getNettoPrice() currency=$currency}]
                    [{if $oPaymentPrice->getVatValue() > 0}]
                        [{oxmultilang ident="PLUS_VAT"}] [{oxprice price=$oPaymentPrice->getVatValue() currency=$currency}]
                    [{/if}])
                [{/strip}]
            [{else}]
                ([{oxprice price=$oPaymentPrice->getBruttoPrice() currency=$currency}])
            [{/if}]
        [{/if}]

        [{foreach from=$paymentmethod->getDynValues() item=value name=PaymentDynValues}]
            <div class="form-group">
                <label class="control-label col-lg-3" for="[{$sPaymentID}]_[{$smarty.foreach.PaymentDynValues.iteration}]">[{$value->name}]</label>
                <div class="col-lg-9">
                    <input id="[{$sPaymentID}]_[{$smarty.foreach.PaymentDynValues.iteration}]" type="text" class="form-control textbox" size="20" maxlength="64" name="dynvalue[[{$value->name}]]" value="[{$value->value}]">
                </div>
            </div>
        [{/foreach}]

        <div class="clearfix"></div>

        [{if $paymentmethod->oxpayments__oxid == "wdcreditcard"}]
          <p>API URL: [{$paymentmethod->oxpayments__wdoxidee_apiurl}]</p>
          <p>Maid: [{$paymentmethod->oxpayments__wdoxidee_maid}]</p>
          <p>Secret: [{$paymentmethod->oxpayments__wdoxidee_secret}]</p>
          <p>3D Maid: [{$paymentmethod->oxpayments__wdoxidee_three_d_maid}]</p>
          <p>3D Secret: [{$paymentmethod->oxpayments__wdoxidee_three_d_secret}]</p>
        [{/if}]

        [{block name="checkout_payment_longdesc"}]
            [{if $paymentmethod->oxpayments__oxlongdesc->value|strip_tags|trim}]
                <div class="alert alert-info col-lg-offset-3 desc">
                    [{$paymentmethod->oxpayments__oxlongdesc->getRawValue()}]
                </div>
            [{/if}]
        [{/block}]
    </dd>
</dl>
